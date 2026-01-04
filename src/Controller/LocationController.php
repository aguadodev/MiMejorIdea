<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/location')]
final class LocationController extends AbstractController
{
    #[Route('/map', name: 'app_location', methods: ['GET', 'POST'])]
    public function map(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $location->setUser($this->getUser());
            $entityManager->persist($location);
            $entityManager->flush();

            // Engadir ID como query param
            return $this->redirect($request->query->get('redirect_to'));
        }

        return $this->render('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }


    #[Route('/ruta/{home}/{work}', name: 'app_location_ruta', methods: ['GET'])]
    public function ruta(EntityManagerInterface $entityManager, int $home, int $work,): Response
    {
        $homeLocation = $entityManager->getRepository(Location::class)->find($home);
        $workLocation = $entityManager->getRepository(Location::class)->find($work);

        if (!$homeLocation || !$workLocation) {
            throw $this->createNotFoundException("Localización non atopada");
        }

        return $this->render('location/ruta.html.twig', [
            'homeLocation' => $homeLocation,
            'workLocation' => $workLocation
        ]);
    }


    #[Route(name: 'app_location_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $locations = $entityManager
            ->getRepository(Location::class)
            ->findByUser($this->getUser());

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
        ]);
    }


    #[Route('/{id}', name: 'app_location_show', methods: ['GET'])]
    public function show(Location $location): Response
    {
        return $this->render('location/show.html.twig', [
            'location' => $location,
        ]);
    }


    #[Route('/{id}', name: 'app_location_delete', methods: ['POST'])]
    public function delete(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        if (!($location->getUser() === null || $location->getUser() === $this->getUser()))
            throw new AccessDeniedHttpException('No tienes permiso.');

        if ($this->isCsrfTokenValid('delete' . $location->getId(), $request->getPayload()->getString('_token'))) {
            // @TODO - Comprobar si la localización se está usando para evitar errores de integridad referencial
            $entityManager->remove($location);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
    }

    /*
    #[Route('/new', name: 'app_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(Location1Type::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }
*/


    /* #[Route('/{id}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Location1Type::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }*/
}
