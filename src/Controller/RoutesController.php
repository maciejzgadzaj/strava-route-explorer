<?php

namespace App\Controller;

use App\Entity\Route as StravaRoute;
use App\Form\PublishType;
use App\Form\RouteAddType;
use App\Form\RouteFilterType;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\PhotonService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RoutesController
 *
 * @package App\Controller
 */
class RoutesController extends ControllerBase
{
    /**
     * List routes.
     *
     * @Route("/routes", name="routes")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function listAction(
        Request $request,
        EntityManagerInterface $entityManager,
        RouteService $routeService,
        AthleteService $athleteService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            $this->addFlash('error', 'Please connect with Strava first to be able to access route listing.');
            $this->container->get('session')->set('strava_redirect_destination', [
                'route' => $request->get('_route'),
                'query' => $request->query->all(),
            ]);
            return $this->redirectToRoute('homepage');
        }
        $this->container->get('session')->remove('strava_redirect_destination');

        // @TODO: Remove in some time.
        $athleteService->removeOldCookies();

        // Add route.
        $routeAddForm = $this->createForm(RouteAddType::class);
        $routeAddForm->handleRequest($request);

        if ($routeAddForm->isSubmitted() && $routeAddForm->isValid()) {
            $routeId = str_replace('https://www.strava.com/routes/', '', $routeAddForm->getData()['route_id']);
            $routeId = preg_replace('/[^0-9]/', '', $routeId);

            return $this->redirectToRoute('routes_add', ['route_id' => $routeId]);
        }

        // Filters.
        $routeFilterForm = $this->createForm(RouteFilterType::class, [
            'route_service' => $routeService,
        ], ['csrf_protection' => false]);
        $routeFilterForm->handleRequest($request);

        $filters = [];
        if ($routeFilterForm->isSubmitted() && $routeFilterForm->isValid()) {
            if ($routeFilterForm->get('reset')->isClicked()) {
                return $this->redirectToRoute('routes');
            }
            $filters = array_filter($routeFilterForm->getData());
        }
        /** @var \App\Repository\RouteRepository $repository */
        $repository = $entityManager->getRepository(StravaRoute::class);

        // Paging.
        $perPage = $request->query->get('per_page', 50);
        $page = $request->query->get('page', 1);

        // Sorting.
        $map = [
            'name' => 'r.name',
            'distance' => 'r.distance',
            'ascent' => 'r.ascent',
            'athlete' => 'a.name',
            'date' => 'r.updatedAt',
        ];

        if ($sort = $request->query->get('sort', null)) {
            $sort = $map[$sort];
            $dir = $request->query->get('dir', 'desc');
            $orderBy = [$sort => $dir];
        }

        // Routes.
        $routes = $repository->findByFilters($filters, $orderBy ?? null, $perPage, ($page - 1) * $perPage);

        return $this->render(
            'routes/list.html.twig',
            [
                'current_athlete' => $athleteService->getCurrentAthlete(),
                'data' => $routes,
                'per_page' => $perPage,
                'pages' => $routes['pages'],
                'route_add_form' => $routeAddForm->createView(),
                'route_filter_form' => $routeFilterForm->createView(),
                'filter_values' => $routeService->getFiltersForDisplay(array_filter($request->query->get('filter', []))),
            ]
        );
    }

    /**
     * Get map thumbnail from MapQuest for a single route.
     *
     * @Route("/images/routes/{athlete_id}/{route_id}.jpg", name="route_map")
     *
     * @param string $athlete_id
     * @param string $route_id
     * @param \App\Service\RouteService $routeService
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getMapsAction(
        $athlete_id,
        $route_id,
        RouteService $routeService,
        MapService $mapService,
        EntityManagerInterface $entityManager
    ) {
        $route = $routeService->load($route_id);

        $map = $mapService->getMap($route);

        $message = 'Fetched map thumbnail from MapQuest for route "%route_name%" (%route_id%) by %athlete%.';
        $params = [
            '%route_name%' => $route->getName(),
            '%route_id%' => $route->getId(),
            '%athlete%' => $route->getAthlete()->getName(),
        ];
        $this->logger->debug(strtr($message, $params));

        return $map;
    }

    /**
     * Add single public route by its id.
     *
     * @Route("/routes/add/{route_id}", name="routes_add")
     *
     * @param string $route_id
     * @param \App\Service\StravaService $stravaService
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addAction(
        $route_id,
        StravaService $stravaService,
        RouteService $routeService,
        AthleteService $athleteService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('homepage');
        }

        if ($route = $routeService->syncRoute($route_id)) {
            $redirectParams = ['filter[name]' => $route->getId()];
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }

    /**
     * Synchronize all currenct user public routes.
     *
     * @Route("/routes/sync", name="routes_sync_mine")
     *
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function syncMineAction(AthleteService $athleteService)
    {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('homepage');
        }

        if (!$athlete = $athleteService->getCurrentAthlete()) {
            return $this->redirectToRoute('routes');
        }

        return $this->redirectToRoute('routes_sync', [
            'athlete_id' => $athlete->getId(),
        ]);
    }

    /**
     * Synchronize all user public routes.
     *
     * @Route("/routes/sync/{athlete_id}", name="routes_sync")
     *
     * @param string $athlete_id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\StravaService $stravaService
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function syncAction(
        $athlete_id,
        Request $request,
        EntityManagerInterface $entityManager,
        StravaService $stravaService,
        RouteService $routeService,
        AthleteService $athleteService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('homepage');
        }

        try {
            if (!$athlete = $athleteService->load($athlete_id)) {
                throw new \Exception(strtr('Athlete %athlete_id% not found.', [
                    '%athlete_id%' => $athlete_id,
                ]));
            }

            $publicAdded = $publicUpdated = $published = $privateSkipped = $privateDeleted = 0;
            $syncedIds = [];

            // Fetch athlete routes from Strava and store them temporarily in cache,
            // so that we don't have to re-fetch them after submitting the form.
            $cache = new FilesystemCache();
            $cacheKey = 'strava.routes.'.$athlete->getId();

            if (!$stravaRoutes = $cache->get($cacheKey)) {
                $stravaRoutes = $stravaService->getAthleteRoutes($athlete);

                // If no routes were fetched from Strava, there is nothing to synchronize,
                // so redirect back to route listing page.
                if (empty($stravaRoutes)) {
                    return $this->getRedirect('routes');
                }

                // The cache item should be deleted after submitting the route select form,
                // but just in case something goes wrong and it doesn't happen,
                // let's also give it a short TTL.
                $cache->set($cacheKey, $stravaRoutes, 3600);
            }

            $localRoutes = $routeService->getAthleteRoutes($athlete);
            $localStarredRoutes = $routeService->getAthleteStarredRoutes($athlete);

            $publishForm = $this->createForm(PublishType::class, [
                'local_routes' => $localRoutes,
                'local_starred_routes' => $localStarredRoutes,
                'strava_routes' => $stravaRoutes,
            ]);
            $publishForm->handleRequest($request);

            if ($publishForm->isSubmitted() && $publishForm->isValid()) {
                $data = $publishForm->getData();

                foreach ($stravaRoutes as $routeData) {
                    $public = in_array($routeData->id, $data['route']) ? true : false;

                    // Save public routes only.
                    if (!$routeData->private) {
                        // Create athlete if it doesn't exist yet.
                        if (!$athleteService->exists($routeData->athlete->id)) {
                            $athleteService->save($routeData->athlete);
                        }

                        // If route belongs to current athlete, just use set its public value.
                        if ($routeData->athlete->id == $athlete->getId()) {
                            $routeData->public = $public;
                            $syncedIds[] = $routeData->id;

                            if ($public) {
                                $published++;
                            }
                        } else {
                            $routeData->public = true;
                        }

                        // Create or update route.
                        $route = $routeService->save($routeData);

                        // If route belongs to a different athlete, and public is set to false
                        // by current athlete, let's just not add starred_by value.
                        if ($route->getAthlete()->getId() != $athlete->getId() && $public) {
                            $route->addStarredBy($athlete);
                            $syncedIds[] = $routeData->id;
                        }

                        if ($route->isNew()) {
                            $publicAdded++;
                        } else {
                            $publicUpdated++;
                        }
                    } else {
                        if ($routeService->exists($routeData->id)) {
                            $routeService->delete($routeData->id);
                            $privateDeleted++;
                        }
                        $privateSkipped++;
                    }
                }

                $publicDeleted = $routeService->deleteAthleteRoutes($athlete, $syncedIds);
                $unstarred = $routeService->unstarAthleteRoutes($athlete, $syncedIds);

                $athlete->setLastSync(new \DateTime());
                $entityManager->flush();

                $cache->delete($cacheKey);

                $this->logger->info(strtr('Synchronized routes for %athlete% (%athlete_id%).', [
                    '%athlete%' => $athlete->getName(),
                    '%athlete_id%' => $athlete->getId(),
                ]));

                $message = 'Routes synchronised: %public_added% new public added, %public_updated% updated
and %public_deleted% deleted, %published% published, %private_skipped% private skipped and %private_deleted% deleted.';
                $params = [
                    '%public_added%' => $publicAdded,
                    '%public_updated%' => $publicUpdated,
                    '%public_deleted%' => $publicDeleted,
                    '%published%' => $published,
                    '%private_skipped%' => $privateSkipped,
                    '%private_deleted%' => $privateDeleted,
                ];
                $this->logger->debug(strtr($message, $params));
                $this->addFlash('notice', 'Routes synchronized.');
                return $this->getRedirect('routes', [
                    'filter[athlete]' => $athlete->getName(),
                    'filter[starred]' => true,
                ]);
            } else {
                $this->addFlash('orange', 'Select routes to publish and share with other athletes:');
            }

            return $this->render(
                'routes/select.html.twig',
                [
                    'strava_routes' => $stravaRoutes,
                    'publish_form' => $publishForm->createView(),
                ]
            );
        } catch (ClientException $e) {
            if (isset($cache)) {
                $cache->delete($cacheKey);
            }
            $content = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents());
            $this->logger->error($content->message);
            $this->addFlash('error', $content->message);
            return $this->getRedirect('routes');
        } catch (\Exception $e) {
            if (isset($cache)) {
                $cache->delete($cacheKey);
            }
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $e->getMessage());
            return $this->getRedirect('routes');
        }

        return $this->render('routes/select.html.twig', ['routes' => []]);
    }

    /**
     * Location autocomplete callback.
     *
     * @Route("/routes/autocomplete/location", name="routes_autocomplete_location")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\AthleteService $athleteService
     * @param \App\Service\PhotonService $photonService
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function autocompleteLocationAction(
        Request $request,
        AthleteService $athleteService,
        PhotonService $photonService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('homepage');
        }

        $name = trim(strip_tags($request->get('term')));

        $names = $photonService->getLocationsByName($name);

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }

    /**
     * Geolocate callback.
     *
     * @Route("/routes/autocomplate/reverse", name="routes_autocomplete_reverse")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\PhotonService $photonService
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function autocompleteReverseAction(Request $request, AthleteService $athleteService, PhotonService $photonService)
    {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('homepage');
        }

        $location = $photonService->reverseGeocode($request->get('lat'), $request->get('lon'));

        $response = new JsonResponse();
        $response->setData($location);

        return $response;
    }

    /**
     * Return redirect stored in session during OAuth.
     *
     * @param string $route
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function getRedirect($route, $parameters = [])
    {
        if ($destination = $this->container->get('session')->get('strava_redirect_destination')) {
            $this->container->get('session')->remove('strava_redirect_destination');
            return $this->redirectToRoute($destination['route'], $destination['query']);
        } else {
            return $this->redirectToRoute($route, $parameters);
        }
    }
}
