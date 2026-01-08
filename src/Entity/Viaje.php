<?php

namespace App\Entity;

use App\Repository\ViajeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ViajeRepository::class)]
class Viaje
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $startLocation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $endLocation = null;


    #[Assert\GreaterThan(
    value: "now +30 minutes",
    message: "La fecha tiene que ser por lo menos 30 minutos posterior a la actual."
    )]
    #[ORM\Column]
    private ?\DateTime $fechaHora = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $conductor = null;


    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "El número de plazas debe estar entre {{ min }} y {{ max }}."
    )]    
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $plazas = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    /**
     * @var Collection<int, ViajeSolicitud>
     */
    #[ORM\OneToMany(targetEntity: ViajeSolicitud::class, mappedBy: 'viaje', orphanRemoval: true)]
    private Collection $solicitudes;

    public function __construct()
    {
        // Inicializa la fecha creación al momento actual
        $this->createdAt = new \DateTimeImmutable();
        // Inicializa la hora de salida del viaje
        $this->fechaHora = new \DateTime('+1 hour');
        $this->solicitudes = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartLocation(): ?Location
    {
        return $this->startLocation;
    }

    public function setStartLocation(?Location $startLocation): static
    {
        $this->startLocation = $startLocation;

        return $this;
    }

    public function getEndLocation(): ?Location
    {
        return $this->endLocation;
    }

    public function setEndLocation(?Location $endLocation): static
    {
        $this->endLocation = $endLocation;

        return $this;
    }

    public function getFechaHora(): ?\DateTime
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTime $fechaHora): static
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getConductor(): ?User
    {
        return $this->conductor;
    }

    public function setConductor(?User $conductor): static
    {
        $this->conductor = $conductor;

        return $this;
    }

    public function getPlazas(): ?int
    {
        return $this->plazas;
    }

    public function setPlazas(int $plazas): static
    {
        $this->plazas = $plazas;

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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ViajeSolicitud>
     */
    public function getSolicitudes(): Collection
    {
        return $this->solicitudes;
    }

    public function addSolicitude(ViajeSolicitud $solicitude): static
    {
        if (!$this->solicitudes->contains($solicitude)) {
            $this->solicitudes->add($solicitude);
            $solicitude->setViaje($this);
        }

        return $this;
    }

    public function removeSolicitude(ViajeSolicitud $solicitude): static
    {
        if ($this->solicitudes->removeElement($solicitude)) {
            // set the owning side to null (unless already changed)
            if ($solicitude->getViaje() === $this) {
                $solicitude->setViaje(null);
            }
        }

        return $this;
    }
}
