<?php

namespace App\Entity;

use App\Repository\IngredienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredienteRepository::class)]
//#[ORM\Table(name: "rec-ingrediente")]
class Ingrediente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $nombre;

    #[ORM\ManyToMany(targetEntity: Receta::class, mappedBy: 'ingredientes')]
    //#[ORM\JoinTable(name: "rec-receta")]
    private $recetas;

    #[ORM\Column(type: 'text', nullable: true)]
    private $notas;

    public function __construct()
    {
        $this->recetas = new ArrayCollection();
    }

    // toString
    public function __toString(): string
    {
        return $this->nombre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection<int, Receta>
     */
    public function getRecetas(): Collection
    {
        return $this->recetas;
    }

    public function addReceta(Receta $receta): self
    {
        if (!$this->recetas->contains($receta)) {
            $this->recetas[] = $receta;
            //$receta->addIngrediente($this);
        }

        return $this;
    }

    public function removeReceta(Receta $receta): self
    {
        $this->recetas->removeElement($receta);

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
}
