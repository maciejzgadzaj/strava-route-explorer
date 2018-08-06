<?php

namespace App\Controller;

use App\Entity\Route as StravaRoute;
use App\Exception\NoticeException;
use App\Form\RouteAddType;
use App\Form\RouteFilterType;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RoutesController
 *
 * @package App\Controller
 */
class RoutesController extends Controller
{
    /**
     * List routes.
     *
     * @Route("/routes", name="routes")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function listAction(Request $request, EntityManagerInterface $entityManager)
    {
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
        $routes = $repository->findByFilters($filters, [$orderBy => $dir], $perPage, ($page-1)*$perPage);

        return $this->render('routes/list.html.twig', [
            'routes' => $routes,
            'per_page' => $perPage,
            'pages' => $routes['pages'],
            'route_add_form' => $routeAddForm->createView(),
            'route_filter_form' => $routeFilterForm->createView(),
        ]);
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
        MapService $mapService,
        EntityManagerInterface $entityManager
    ) {
        $map = $mapService->loadByRouteId($route_id);

        return $mapService->getMap($map);
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
     * @param \App\Service\MapService $mapService
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
        AthleteService $athleteService,
        MapService $mapService
    ) {
        try {
            // To fetch single route details we can use generic app access token.
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getParameter('strava_access_token'),
                ],
            ];
            $response = $stravaService->apiRequest('get', '/api/v3/routes/'.$route_id, $options);
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

            if (!empty($content->private)) {
                // If a private route is found in our database, let's delete it.
                if ($routeService->exists($content->id)) {
                    $routeService->delete($content->id);

                    throw new NoticeException(
                        strtr(
                            'Deleted private route "%route_name%" (%route_id%) by %athlete%.',
                            [
                                '%route_name%' => $content->name,
                                '%route_id%' => $content->id,
                                '%athlete%' => $content->athlete->firstname.' '.$content->athlete->lastname,
                            ]
                        )
                    );
                }

                throw new \Exception('Cowardly refusing to add a private route.');
            }

            if (!$athleteService->exists($content->athlete->id)) {
                $athleteService->save($content->athlete);
            }

            // Save route.
            $route = $routeService->save($content);

            // Save map details.
            $map = $mapService->save($content->map);

            $this->addFlash(
                'notice',
                strtr(
                    'Route "%route_name%" (%route_id%) by %athlete% %action%.',
                    [
                        '%route_name%' => $content->name,
                        '%route_id%' => $content->id,
                        '%athlete%' => $content->athlete->firstname.' '.$content->athlete->lastname,
                        '%action%' => $route->isNew() ? 'added' : 'updated',
                    ]
                )
            );

            $redirectParams = ['filter[name]' => $route->getId()];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());
            $this->addFlash('error', $content->message);

            // Delete local route if it was not found on Strava.
            if ($localRoute = $routeService->load($route_id)) {
                $routeService->delete($localRoute->getId());

                $this->addFlash(
                    'notice',
                    strtr(
                        'Deleted route "%route_name%" (%route_id%) by %athlete% not found on Strava.',
                        [
                            '%route_name%' => $localRoute->getName(),
                            '%route_id%' => $localRoute->getId(),
                            '%athlete%' => $localRoute->getAthlete()->getName(),
                        ]
                    )
                );
            }
        } catch (NoticeException $e) {
            $this->addFlash('notice', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }

    /**
     * Synchronize all user public routes.
     *
     * @Route("/routes/sync", name="routes_sync")
     *
     * @param \App\Service\StravaService $stravaService
     * @param \App\Service\RouteService $routeService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function syncAction(
        SessionInterface $session,
        StravaService $stravaService,
        RouteService $routeService,
        AthleteService $athleteService
    ) {
        try {
            // We don't know who is using the service.
            if (!$athlete = $session->get('athlete')) {
                return $this->redirectToRoute('strava_auth');
            }

            $added = $updated = $skipped = $deleted = 0;
            $page = 1;

            do {
                $response = $stravaService->apiRequest(
                    'get',
                    '/api/v3/athletes/'.$athlete->id.'/routes?per_page=200&page='.$page
                );
                $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

                foreach ($content as $routeData) {
                    if (!$routeData->private) {
                        // Create athlete if it doesn't exist yet.
                        if (!$athleteService->exists($routeData->athlete->id)) {
                            $athleteService->save($routeData->athlete);
                        }

                        // @TODO: map and climbs.

                        // Create or update route.
                        $route = $routeService->save($routeData);
                        if ($route->isNew()) {
                            $added++;
                        } else {
                            $updated++;
                        }
                    } else {
                        if ($routeService->exists($routeData->id)) {
                            $routeService->delete($routeData->id);
                            $deleted++;
                        }
                        $skipped++;
                    }
                }

                $page++;
            } while (!empty($content));

            $this->addFlash(
                'notice',
                strtr(
                    'Routes synchronised: %added% new public added and %updated% updated, 
                    %skipped% private skipped and %deleted% deleted.',
                    [
                        '%added%' => $added,
                        '%updated%' => $updated,
                        '%skipped%' => $skipped,
                        '%deleted%' => $deleted,
                    ]
                )
            );

            $redirectParams = ['filter[athlete]' => $athlete->id];
        } catch (ClientException $e) {
            $content = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents());
            $this->addFlash('error', $content->message);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }
}
