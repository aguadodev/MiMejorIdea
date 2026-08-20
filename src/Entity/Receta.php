<?php

namespace App\Entity;

use App\Repository\RecetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetaRepository::class)]
//#[ORM\Table(name: "rec-receta")]
class Receta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $titulo;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    #[ORM\Column(type: 'text', nullable: true)]
    private $notas;

    #[ORM\ManyToMany(targetEntity: Ingrediente::class, inversedBy: 'recetas')]
    //#[ORM\JoinTable(name: "rec-ingrediente")]
    private $ingredientes;

    public function __construct()
    {
        $this->ingredientes = new ArrayCollection();
    }

    // toString
    public function __toString(): string
    {
        return $this->titulo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(?string $notas): self
    {
        $this->notas = $notas;

        return $this;
    }

    /**
     * @return Collection<int, Ingrediente>
     */
    public function getIngredientes(): Collection
    {
        return $this->ingredientes;
    }

    public function addIngrediente(Ingrediente $ingrediente): self
    {
        if (!$this->ingredientes->contains($ingrediente)) {
            $this->ingredientes[] = $ingrediente;
            $ingrediente->addReceta($this);
        }

        return $this;
    }

    public function removeIngrediente(Ingrediente $ingrediente): self
    {
        if ($this->ingredientes->removeElement($ingrediente)) {
            $ingrediente->removeReceta($this);
        }

        return $this;
    }
}
