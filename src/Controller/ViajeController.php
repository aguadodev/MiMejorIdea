<?php

namespace App\Controller;

use App\Entity\Viaje;
use App\Enum\ViajeSolicitudEstado;
use App\Form\ViajeType;
use App\Repository\ViajeRepository;
use App\Service\Mail;
use DateTimeImmutable;
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


    #[Route('/rutas_proximas', name: 'app_rutas_proximas', methods: ['GET'])]
    public function rutasProximos(ViajeRepository $viajeRepository): Response
    {
        return $this->render('viaje/proximas_rutas.html.twig', [
            'viajes' => $viajeRepository->findProximosViajes(),
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
            $viaje->publicar();

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

    #[IsGranted('ROLE_USER')]
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
            $viaje->setUpdatedAt(new \DateTime()); // Fecha última modificación
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
    public function delete(Request $request, Viaje $viaje, EntityManagerInterface $entityManager, Mail $mail): Response
    {
        // Solo el conductor puede borrar/cancelar un viaje
        if (!($viaje->getConductor() === $this->getUser()))
            throw new AccessDeniedHttpException('No tienes permiso.');

        // @TODO Si el viaje ya ha finalizado no se puede cancelar.

        if ($this->isCsrfTokenValid('delete' . $viaje->getId(), $request->getPayload()->getString('_token'))) {
            if ($viaje->tieneSolicitudes()) {
                // @TODO Si el viaje tiene solicitudes pendientes
                // Se rechazan por cancelación de viaje y se notifica a los posibles pasajeros
                if ($viaje->tieneSolicitudesPendientes()) {
                    foreach ($viaje->getSolicitudesPendientes() as $solicitud) {
                        // Cancelar solicitud
                        $solicitud->setEstado(ViajeSolicitudEstado::RECHAZADA_POR_CANCELACION_VIAJE);
                        // Enviar notificación por mail al pasajero
                        $mail->enviarMailViajeCancelado($solicitud);
                        $this->addFlash('success', 'Viaje cancelado correctamente.');
                    }
                }

                // @TODO Si el viaje tiene solicitudes aceptadas
                // Se cancelan por cancelación de viaje y se notifica a los pasajeros
                if ($viaje->tieneSolicitudesAceptadas()) {
                    foreach ($viaje->getSolicitudesAceptadas() as $solicitud) {
                        // Cancelar solicitud
                        $solicitud->setEstado(ViajeSolicitudEstado::CANCELADA_POR_CONDUCTOR);
                        // Enviar notificación por mail al pasajero
                        $mail->enviarMailViajeCancelado($solicitud);
                        $this->addFlash('success', 'Viaje cancelado correctamente.');
                    }
                }

                // Cancelar Viaje
                $viaje->setUpdatedAt(new \DateTime()); // Fecha última modificación
                // Cambiar estado
                $viaje->cancelar();
                $entityManager->flush();
            } else {
                // Si el viaje no tiene solicitudes ni interacciones
                // Se borra directamente
                $entityManager->remove($viaje);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('viajes_usuario', [], Response::HTTP_SEE_OTHER);
    }
}
