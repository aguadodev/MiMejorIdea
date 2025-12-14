<?php

namespace App\Controller;

use App\Entity\Viaje;
use App\Entity\ViajeSolicitud;
use App\Enum\ViajeSolicitudEstado;
use App\Form\ViajeSolicitudType;
use App\Repository\ViajeSolicitudRepository;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/viajesolicitud')]
final class ViajeSolicitudController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    use Helper; // trait con código reutilizable para controladores


    #[Route(name: 'app_viaje_solicitud_index', methods: ['GET'])]
    public function index(ViajeSolicitudRepository $viajeSolicitudRepository): Response
    {
        return $this->render('viaje_solicitud/index.html.twig', [
            'viaje_solicituds' => $viajeSolicitudRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'viaje_solicitud_new', methods: ['GET', 'POST'])]
    public function nuevaSolicitud(Viaje $viaje, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        // Comprobar si se puede solicitar el viaje.
        if ($viaje->getFechaHora() <= $now) {
            return $this->denyAndBack($request, 'No se puede solicitar un viaje que ya pasó.');
        }

        if ($viaje->getConductor() === $user) {
            return $this->denyAndBack($request, 'No puedes solicitar un viaje del que eres conductor.');
        }

        if (!$user->isVerified()) {
            $message = 'Debes verificar tu cuenta para acceder a esta funcionalidad. <a href="' . $this->generateUrl('app_resend_email') . '">Verificar ahora</a>';
            return $this->denyAndBack($request, $message);
        }

        /* Si el usuario ya ha solicitado este viaje no puede solicitarlo de nuevo */
        foreach ($viaje->getSolicitudes() as $solicitud) {
            if ($solicitud->getPasajero() === $this->getUser()) {
                // O usuario actual xa ten unha solicitude neste viaxe
                return $this->denyAndBack($request, 'Ya has solicitado este viaje. No puedes solicitarlo de nuevo.');
            }
        }

        /* TODO
        if ($viaje->estaCompleto()) {
            return $this->denyAndBack($request, 'El viaje ya está completo.');
        }*/

        $viajeSolicitud = new ViajeSolicitud();
        $viajeSolicitud->setViaje($viaje);
        $viajeSolicitud->setPasajero($user);

        $form = $this->createForm(ViajeSolicitudType::class, $viajeSolicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($viajeSolicitud);
            $entityManager->flush();

            // Enviar notificación por mail al conductor
            $mail = new Mail($mailer);
            $mail->enviarMailSolicitudViaje($viajeSolicitud);

            return $this->redirectToRoute('app_viaje_show', ['id' => $viaje->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('viaje_solicitud/new.html.twig', [
            'viaje_solicitud' => $viajeSolicitud,
            'form' => $form,
        ]);
    }


    #[Route('/solicitud/{id}/aceptar/{token}', name: 'viaje_solicitud_aceptar')]
    public function aceptar(ViajeSolicitud $solicitud, string $token, EntityManagerInterface $em): Response
    {
        if ($solicitud->getToken() !== $token) {
            throw $this->createAccessDeniedException();
        }

        if ($solicitud->getEstado() !== ViajeSolicitudEstado::PENDIENTE) {
            $this->addFlash('info', 'Esta solicitud ya ha sido procesada.');
            return $this->redirectToRoute('app_viaje_solicitud_show', ['id' => $solicitud->getId()]);
        }

        $solicitud->aceptar();
        $em->flush();

        // Enviar notificación por mail al pasajero
        $mail = new Mail($this->mailer);
        $mail->enviarMailSolicitudViajeAceptada($solicitud);

        $this->addFlash('success', 'Solicitud aceptada correctamente.');

        return $this->redirectToRoute('app_viaje_show', ['id' => $solicitud->getViaje()->getId()]);
    }


    #[Route('/solicitud/{id}/rechazar/{token}', name: 'viaje_solicitud_rechazar')]
    public function rechazar(ViajeSolicitud $solicitud, string $token, EntityManagerInterface $em): Response
    {
        if ($solicitud->getToken() !== $token) {
            throw $this->createAccessDeniedException();
        }

        if ($solicitud->getEstado() !== ViajeSolicitudEstado::PENDIENTE) {
            $this->addFlash('info', 'Esta solicitud ya ha sido procesada.');
            return $this->redirectToRoute('app_viaje_solicitud_show', ['id' => $solicitud->getId()]);
        }

        $solicitud->rechazar();
        $em->flush();

        // TODO Enviar notificación por mail al pasajero
        //$mail = new Mail($this->mailer);
        //$mail->enviarMailSolicitudViajeAceptada($solicitud);        

        $this->addFlash('success', 'Solicitud rechazada correctamente.');

        return $this->redirectToRoute('viajes_usuario');
    }


    #[Route('/show/{id}', name: 'app_viaje_solicitud_show', methods: ['GET'])]
    public function show(ViajeSolicitud $viajeSolicitud): Response
    {
        $user = $this->getUser();

        if (!($viajeSolicitud->getViaje()->getConductor() === $user || $viajeSolicitud->getPasajero() === $user)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('viaje_solicitud/show.html.twig', [
            'viaje_solicitud' => $viajeSolicitud,
        ]);
    }    

    #[Route('/{id}', name: 'app_viaje_solicitud_delete', methods: ['POST'])]
    public function delete(Request $request, ViajeSolicitud $viajeSolicitud, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $viajeSolicitud->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($viajeSolicitud);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_viaje_solicitud_index', [], Response::HTTP_SEE_OTHER);
    }


    /*
    #[Route('/new', name: 'viaje_solicitud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $viajeSolicitud = new ViajeSolicitud();
        $form = $this->createForm(ViajeSolicitudType::class, $viajeSolicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($viajeSolicitud);
            $entityManager->flush();

            return $this->redirectToRoute('app_viaje_solicitud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('viaje_solicitud/new.html.twig', [
            'viaje_solicitud' => $viajeSolicitud,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_viaje_solicitud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ViajeSolicitud $viajeSolicitud, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ViajeSolicitudType::class, $viajeSolicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_viaje_solicitud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('viaje_solicitud/edit.html.twig', [
            'viaje_solicitud' => $viajeSolicitud,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_viaje_solicitud_delete', methods: ['POST'])]
    public function delete(Request $request, ViajeSolicitud $viajeSolicitud, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$viajeSolicitud->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($viajeSolicitud);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_viaje_solicitud_index', [], Response::HTTP_SEE_OTHER);
    }
        */

    // Métodos auxiliares // TODO Reutilizar en otros controladores
    /* private function denyAndBack(Request $request, string $message, string $type = 'warning'): Response
    {
        $this->addFlash($type, $message);

        return $this->redirect(
            $request->headers->get('referer')
                ?? $this->generateUrl('app_index')
        );
    }*/
}
