<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Entity\Athlete;
use App\Form\ManageMyRoutesForm;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/manage-my-routes', name: 'manage_my_routes')]
#[IsGranted('ROLE_USER')]
class ManageMyRoutes extends AbstractController
{
    public function __invoke(
        Request $request,
        Security $security,
        RouteService $routeService,
        EntityManagerInterface $entityManager,
    ): Response|RedirectResponse {
        /** @var Athlete $currentAthlete */
        $currentAthlete = $security->getUser();

        $localRoutes = $routeService->getAthleteRoutes($currentAthlete);
        $localStarredRoutes = $routeService->getAthleteStarredRoutes($currentAthlete);

        $manageRoutesForm = $this->createForm(ManageMyRoutesForm::class, [
            'local_routes' => $localRoutes,
            'local_starred_routes' => $localStarredRoutes,
        ]);
        $manageRoutesForm->handleRequest($request);

        if ($manageRoutesForm->isSubmitted() && $manageRoutesForm->isValid()) {
            $formData = $manageRoutesForm->getData();

            // Process athlete's own routes.
            foreach ($localRoutes as $localRoute) {
                $public = in_array($localRoute->getId(), $formData['route']) ? true : false;

                // The route always belongs to the current athlete,
                // so we can set its public value from the submitted form.
                if ($public !== $localRoute->isPublic()) {
                    $localRoute->setPublic($public);
                    $entityManager->persist($localRoute);
                }
            }

            // @TODO: Process athlete's starred routes.

            $currentAthlete->setLastSync(new \DateTime());
            $entityManager->persist($currentAthlete);

            $entityManager->flush();

            $this->addFlash('notice', 'Your routes have been successfully updated.');

            return $this->redirectToRoute('routes', [
                'filter[athlete]' => $currentAthlete->getName(),
            ]);
        }
        else {
            $this->addFlash('orange', 'Select routes to publish and share with other athletes');
        }

        return $this->render('routes/manage-my-routes.html.twig', [
            'local_routes' => $localRoutes,
            'manage_my_routes_form' => $manageRoutesForm->createView(),
        ]);
    }
}
