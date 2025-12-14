<?php

namespace App\Entity;

use App\Enum\ViajeSolicitudEstado;
use App\Repository\ViajeSolicitudRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ViajeSolicitudRepository::class)]
class ViajeSolicitud
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'solicitudes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Viaje $viaje = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $pasajero = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(enumType: ViajeSolicitudEstado::class)]
    private ?ViajeSolicitudEstado $estado = null;

    #[ORM\Column(length: 64)]
    private ?string $token = null;

    public function __construct()
    {
        // Inicializa la fecha creación al momento actual
        $this->createdAt = new \DateTimeImmutable();
        // Genera un token para aceptar o rechazar la solicitud desde el mail con seguridad
        $this->token = bin2hex(random_bytes(32));
        // Inicializa el estado de una nueva solicitud a PENDIENTE
        $this->estado = ViajeSolicitudEstado::PENDIENTE;
    }


    public function aceptar(): void
    {
        if ($this->estado !== ViajeSolicitudEstado::PENDIENTE) {
            throw new \LogicException('La solicitud ya ha sido procesada.');
        }

        $this->estado = ViajeSolicitudEstado::ACEPTADA;
    }

    public function rechazar(): void
    {
        if ($this->estado !== ViajeSolicitudEstado::PENDIENTE) {
            throw new \LogicException('La solicitud ya ha sido procesada.');
        }

        $this->estado = ViajeSolicitudEstado::RECHAZADA;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViaje(): ?Viaje
    {
        return $this->viaje;
    }

    public function setViaje(?Viaje $viaje): static
    {
        $this->viaje = $viaje;

        return $this;
    }

    public function getPasajero(): ?User
    {
        return $this->pasajero;
    }

    public function setPasajero(?User $pasajero): static
    {
        $this->pasajero = $pasajero;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEstado(): ?ViajeSolicitudEstado
    {
        return $this->estado;
    }

    public function setEstado(ViajeSolicitudEstado $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function isPendiente(): bool
    {
        return $this->estado == ViajeSolicitudEstado::PENDIENTE;
    }

    public function isAceptada(): bool
    {
        return $this->estado == ViajeSolicitudEstado::ACEPTADA;
    }

    public function isRechazada(): bool
    {
        return $this->estado == ViajeSolicitudEstado::RECHAZADA;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
}
