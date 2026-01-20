<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\ViajeRepository;
use App\Repository\ViajeSolicitudRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserServices
{
    public function __construct(
        private ViajeRepository $viajeRepository,
        private ViajeSolicitudRepository $viajeSolicitudRepository,
        private EntityManagerInterface $em,

        // no futuro: EventRepository, MessageRepository, RatingRepository...
    ) {}

    /**
     * Devuelve si el usuario ha tenido interacciones con otros usuarios en el sitio web
     */
    public function hasInteractions(User $user): bool
    {
        return
            $this->viajeRepository->userHasTrips($user) || 
            $this->viajeSolicitudRepository->userHasRequests($user)
            ;
    }

   public function deleteOrAnonymize(User $user): void
    {
        if ($this->hasInteractions($user)) {
            $user->anonymize();
        } else {
            $this->em->remove($user);
        }
        // @TODO Borrar fichero de imagen de perfil
        // $this->photoStorage->delete($user->getPhoto());
        // @TODO Cerrar sesión

    }

}