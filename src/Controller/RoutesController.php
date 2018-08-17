<?php

namespace App\Controller;

use App\Entity\Route as StravaRoute;
use App\Exception\NoticeException;
use App\Form\RouteAddType;
use App\Form\RouteFilterType;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\OpenStreetMapService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
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
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function listAction(
        Request $request,
        EntityManagerInterface $entityManager,
        AthleteService $athleteService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('strava_auth');
        }

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
        $routeFilterForm = $this->createForm(RouteFilterType::class, null, ['csrf_protection' => false]);
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
            'elevation' => 'r.elevationGain',
            'athlete' => 'a.name',
            'date' => 'r.updatedAt',
        ];
        $orderBy = $map[$request->query->get('sort', 'date')];
        $dir = $request->query->get('dir', 'desc');

        // Routes.
        $routes = $repository->findByFilters($filters, [$orderBy => $dir], $perPage, ($page - 1) * $perPage);

        return $this->render(
            'routes/list.html.twig',
            [
                'current_athlete' => $athleteService->getCurrentAthlete(),
                'data' => $routes,
                'per_page' => $perPage,
                'pages' => $routes['pages'],
                'route_add_form' => $routeAddForm->createView(),
                'route_filter_form' => $routeFilterForm->createView(),
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
            return $this->redirectToRoute('strava_auth');
        }

        if ($route = $routeService->syncRoute($route_id)) {
            $redirectParams = ['filter[name]' => $route->getId()];
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }

    /**
     * Synchronize all user public routes.
     *
     * @Route("/routes/sync/{athlete_id}", name="routes_sync")
     *
     * @param string $athlete_id
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
        EntityManagerInterface $entityManager,
        StravaService $stravaService,
        RouteService $routeService,
        AthleteService $athleteService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('strava_auth');
        }

        try {
            if (!$athlete = $athleteService->load($athlete_id)) {
                throw new \Exception(strtr('Athlete %athlete_id% not found.', [
                    '%athlete_id%' => $athlete_id,
                ]));
            }

            // Athlete has not connected his Strava account yet.
            if (!$athleteAccessToken = $athlete->getAccessToken()) {
                throw new \Exception(strtr('No token found for athlete %athlete_id%.', [
                    '%athlete_id%' => $athlete_id,
                ]));
            }

            $publicAdded = $publicUpdated = $privateSkipped = $privateDeleted = 0;
            $syncedIds = [];
            $page = 1;

            do {
                // Use athlete-specific access token to fetch their routes.
                $options = [
                    'headers' => [
                        'Authorization' => 'Bearer '.$athleteAccessToken,
                    ],
                ];
                $response = $stravaService->apiRequest(
                    'get',
                    '/api/v3/athletes/'.$athlete->getId().'/routes?per_page=200&page='.$page,
                    $options
                );
                $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

                foreach ($content as $routeData) {
                    // Save public routes only.
                    if (!$routeData->private) {
                        // Create athlete if it doesn't exist yet.
                        if (!$athleteService->exists($routeData->athlete->id)) {
                            $athleteService->save($routeData->athlete);
                        }

                        // Create or update route.
                        $route = $routeService->save($routeData);

                        if ($route->getAthlete()->getId() != $athlete->getId()) {
                            $route->addStarredBy($athlete);
                        }

                        if ($route->isNew()) {
                            $publicAdded++;
                        } else {
                            $publicUpdated++;
                        }

                        $syncedIds[] = $routeData->id;
                    } else {
                        if ($routeService->exists($routeData->id)) {
                            $routeService->delete($routeData->id);
                            $privateDeleted++;
                        }
                        $privateSkipped++;
                    }
                }

                $page++;
            } while (!empty($content));

            $publicDeleted = $routeService->deleteAthleteRoutes($athlete, $syncedIds);
            $unstarred = $routeService->unstarAthleteRoutes($athlete, $syncedIds);

            $athlete->setLastSync(new \DateTime());
            $entityManager->flush();

            $this->logger->info(strtr('Synchronized routes for %athlete% (%athlete_id%).', [
                '%athlete%' => $athlete->getName(),
                '%athlete_id%' => $athlete->getId(),
            ]));

            $message = 'Routes synchronised: %public_added% new public added, %public_updated% updated 
and %public_deleted% deleted, %private_skipped% private skipped and %private_deleted% deleted.';
            $params = [
                '%public_added%' => $publicAdded,
                '%public_updated%' => $publicUpdated,
                '%public_deleted%' => $publicDeleted,
                '%private_skipped%' => $privateSkipped,
                '%private_deleted%' => $privateDeleted,
            ];
            $this->logger->debug(strtr($message, $params));
            $this->addFlash('notice', strtr($message, $params));

            $redirectParams = [
                'filter[athlete]' => $athlete->getName(),
                'filter[starred]' => true,
            ];
        } catch (ClientException $e) {
            $content = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents());
            $this->logger->error($content->message);
            $this->addFlash('error', $content->message);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }

    /**
     * Location autocomplete callback.
     *
     * @Route("/routes/autocomplete/location", name="routes_autocomplete_location")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\AthleteService $athleteService
     * @param \App\Service\OpenStreetMapService $openStreetMapService
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function autocompleteLocationAction(
        Request $request,
        AthleteService $athleteService,
        OpenStreetMapService $openStreetMapService
    ) {
        // Allow access only to athletes authorized with Strava.
        if (empty($this->getParameter('open_access')) && !$athleteService->isAuthorized()) {
            return $this->redirectToRoute('strava_auth');
        }

        $name = trim(strip_tags($request->get('term')));

        $names = $openStreetMapService->getLocationsByName($name);

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}
