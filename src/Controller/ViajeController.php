<?php

namespace App\Controller;

use App\Entity\Viaje;
use App\Form\ViajeType;
use App\Repository\ViajeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/viaje')]
final class ViajeController extends AbstractController
{
    use Helper; // trait con código reutilizable para controladores

    
    #[Route(name: 'viajes_proximos', methods: ['GET'])]
    public function viajesProximos(ViajeRepository $viajeRepository): Response
    {
        return $this->render('viaje/proximos.html.twig', [
            'viajes' => $viajeRepository->findProximosViajes(),
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/user', name: 'viajes_usuario', methods: ['GET'])]
    public function viajesUsuario(ViajeRepository $viajeRepository): Response
    {
        return $this->render('viaje/index.html.twig', [
            'viajes' => $viajeRepository->findByConductor($this->getUser()),
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_viaje_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->getUsername()) {
            $message = 'Necesitas un nombre de usuario público para interactuar con usuari@s. <a href="' . $this->generateUrl('app_profile_edit') . '">Edita tu perfil</a>';
            return $this->denyAndBack($request, $message);
        }

        $viaje = new Viaje();
        $viaje->setConductor($user);
        if ($user->getPerfilPersonal() != null) {
            $viaje->setStartLocation($user->getPerfilPersonal()->getHomeLocation());
            $viaje->setEndLocation($user->getPerfilPersonal()->getWorkLocation());
        }

        $form = $this->createForm(ViajeType::class, $viaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $viaje->setConductor($user);
            
            $entityManager->persist($viaje);
            $entityManager->flush();

            // @TODO Crear Perfil de Conductor??
            return $this->redirectToRoute('viajes_usuario', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('viaje/new.html.twig', [
            'viaje' => $viaje,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_viaje_show', methods: ['GET'])]
    public function show(Viaje $viaje): Response
    {
        return $this->render('viaje/show.html.twig', [
            'viaje' => $viaje,
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_viaje_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Viaje $viaje, EntityManagerInterface $entityManager): Response
    {
        // Si no es el conductor del viaje: flash warning o excepción?
        if (!($viaje->getConductor() === $this->getUser())) {
            $this->addFlash('warning', 'Debes verificar a túa conta para acceder a esta funcionalidade.');

            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_index'));
            throw new AccessDeniedHttpException('No tienes permiso.');
        }

        $form = $this->createForm(ViajeType::class, $viaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('viajes_proximos', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('viaje/edit.html.twig', [
            'viaje' => $viaje,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_viaje_delete', methods: ['POST'])]
    public function delete(Request $request, Viaje $viaje, EntityManagerInterface $entityManager): Response
    {
        if (!($viaje->getConductor() === $this->getUser()))
            throw new AccessDeniedHttpException('No tienes permiso.');

        if ($this->isCsrfTokenValid('delete' . $viaje->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($viaje);
            $entityManager->flush();
        }

        return $this->redirectToRoute('viajes_proximos', [], Response::HTTP_SEE_OTHER);
    }
}
