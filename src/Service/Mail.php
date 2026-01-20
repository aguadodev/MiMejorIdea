<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\ViajeSolicitud;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class Mail {

    public function __construct(
        private MailerInterface $mailer,
        private EmailVerifier $emailVerifier
    ) {}     

    /**
     * Envía email de verificación del correo electrónico
     * Utilizado en el registro de usuario y al modificar el valor del email
     */
    public function sendEmailConfirmation(User $user)
    {
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
                ->to((string) $user->getEmail())
                ->subject('💡 Verifica tu Correo Electrónico')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }


    public function sendPasswordResetEMail(User $user, ResetPasswordToken $resetToken)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
            ->to((string) $user->getEmail())
            ->subject('💡 Resetea tu contraseña')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $this->mailer->send($email);        
    }    


    public function enviarMailSolicitudViaje(ViajeSolicitud $viajeSolicitud) {
        $email = (new TemplatedEmail())
            ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
            ->to((string) $viajeSolicitud->getPasajero()->getEmail())
            ->subject('💡 Solicitud de Viaje')
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
            ->to((string) $viajeSolicitud->getPasajero()->getEmail())
            ->subject('💡 Solicitud de Viaje Aceptada!')
            ->htmlTemplate('viaje_solicitud/email_solicitud_aceptada.html.twig')
            ->context([
                'viajeSolicitud' => $viajeSolicitud,
            ])
        ;

        $this->mailer->send($email);        
    }

}