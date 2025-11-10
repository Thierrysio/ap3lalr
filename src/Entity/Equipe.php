<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxJoueurs = null;

    #[ORM\Column]
    private ?float $point = null;

    #[ORM\Column(length: 255)]
    private ?string $nomEquipe = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?int $nbIndice = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $lesUsers;

    public function __construct()
    {
        $this->lesUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxJoueurs(): ?int
    {
        return $this->maxJoueurs;
    }

    public function setMaxJoueurs(int $maxJoueurs): static
    {
        $this->maxJoueurs = $maxJoueurs;

        return $this;
    }

    public function getPoint(): ?float
    {
        return $this->point;
    }

    public function setPoint(float $point): static
    {
        $this->point = $point;

        return $this;
    }

    public function getNomEquipe(): ?string
    {
        return $this->nomEquipe;
    }

    public function setNomEquipe(string $nomEquipe): static
    {
        $this->nomEquipe = $nomEquipe;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNbIndice(): ?int
    {
        return $this->nbIndice;
    }

    public function setNbIndice(int $nbIndice): static
    {
        $this->nbIndice = $nbIndice;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLesUsers(): Collection
    {
        return $this->lesUsers;
    }

    public function addLesUser(User $lesUser): static
    {
        if (!$this->lesUsers->contains($lesUser)) {
            $this->lesUsers->add($lesUser);
        }

        return $this;
    }

    public function removeLesUser(User $lesUser): static
    {
        $this->lesUsers->removeElement($lesUser);

        return $this;
    }
}
