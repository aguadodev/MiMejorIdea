<?php

namespace App\Service;

use App\Entity\ViajeSolicitud;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mail {

    public function __construct(
        private MailerInterface $mailer
    ) {}    

    public function enviarMailSolicitudViaje(ViajeSolicitud $viajeSolicitud) {
        $email = (new TemplatedEmail())
            ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
            ->to((string) $viajeSolicitud->getViaje()->getConductor()->getEmail())
            ->subject('Solicitud de Viaje')
            ->htmlTemplate('viaje_solicitud/email_solicitud.html.twig')
            ->context([
                'viajeSolicitud' => $viajeSolicitud,
            ])
        ;

        $this->mailer->send($email);        
    }

    public function enviarMailSolicitudViajeAceptada(ViajeSolicitud $viajeSolicitud) {
        $email = (new TemplatedEmail())
            ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
            ->to((string) $viajeSolicitud->getViaje()->getConductor()->getEmail())
            ->subject('Solicitud de Viaje Aceptada!')
            ->htmlTemplate('viaje_solicitud/email_solicitud_aceptada.html.twig')
            ->context([
                'viajeSolicitud' => $viajeSolicitud,
            ])
        ;

        $this->mailer->send($email);        
    }

}