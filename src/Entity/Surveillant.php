<?php

namespace App\Entity;

use App\Repository\SurveillantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveillantRepository::class)]
class Surveillant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    /**
     * @var Collection<int, Epreuve>
     */
    #[ORM\ManyToMany(targetEntity: Epreuve::class, inversedBy: 'lesSurveillants')]
    private Collection $lesEpreuves;

    public function __construct()
    {
        $this->lesEpreuves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection<int, Epreuve>
     */
    public function getLesEpreuves(): Collection
    {
        return $this->lesEpreuves;
    }

    public function addLesEpreufe(Epreuve $lesEpreufe): static
    {
        if (!$this->lesEpreuves->contains($lesEpreufe)) {
            $this->lesEpreuves->add($lesEpreufe);
        }

        return $this;
    }

    public function removeLesEpreufe(Epreuve $lesEpreufe): static
    {
        $this->lesEpreuves->removeElement($lesEpreufe);

        return $this;
    }
}
