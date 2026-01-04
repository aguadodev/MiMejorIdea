<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Entity\User;



class LoginSubscriber implements EventSubscriberInterface
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        // Obtiene el usuario que ha iniciado sesión
        $user = $event->getAuthenticatedToken()->getUser();

        // Actualiza la fecha de último acceso
        $user->setLastLogin(new \DateTime());
        // Persiste el usuario en la base de datos
        $this->entityManager->flush();
    }

    public function onCheckPassportEvent(CheckPassportEvent $event): void
    {
        // Obtiene el usuario que intenta iniciar sesión
        $user = $event->getPassport()->getUser();
        // Si el usuario no está verificado lanzamos una excepción
        if (!$user->isVerified()) {
            throw new AuthenticationException();
        }
        // Si el usuario no está habilitado lanzamos una excepción
        if (!$user->isEnabled()) {
            throw new AuthenticationException();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
            CheckPassportEvent::class => 'onCheckPassportEvent',
        ];
    }
}
