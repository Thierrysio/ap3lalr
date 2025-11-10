<?php

namespace App\Entity;

use App\Repository\NiveauScolaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NiveauScolaireRepository::class)]
class NiveauScolaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomClasse = null;

    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'User')]
    private Collection $lesUsers;

    public function __construct()
    {
        $this->lesUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomClasse(): ?string
    {
        return $this->nomClasse;
    }

    public function setNomClasse(string $nomClasse): static
    {
        $this->nomClasse = $nomClasse;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): static
    {
        $this->annee = $annee;

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
            $lesUser->setUser($this);
        }

        return $this;
    }

    public function removeLesUser(User $lesUser): static
    {
        if ($this->lesUsers->removeElement($lesUser)) {
            // set the owning side to null (unless already changed)
            if ($lesUser->getUser() === $this) {
                $lesUser->setUser(null);
            }
        }

        return $this;
    }
}
