<?php

namespace App\Entity;

use App\Repository\EpreuveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpreuveRepository::class)]
class Epreuve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomEpreuve = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?\DateTime $duree = null;

    #[ORM\Column]
    private ?int $difficulte = null;

    #[ORM\Column]
    private ?float $pointEpreuve = null;

    #[ORM\Column(length: 255)]
    private ?string $lieuEpreuve = null;

    #[ORM\Column(length: 255)]
    private ?string $typeEpreuve = null;

    #[ORM\Column]
    private ?int $nbIndiceAGagner = null;

    #[ORM\Column]
    private ?\DateTime $dateEpreuveDebut = null;

    #[ORM\Column]
    private ?\DateTime $dateEpreuveFin = null;

    #[ORM\Column]
    private ?float $coeffAnnee = null;

    #[ORM\ManyToOne]
    private ?Score $leScore = null;

    /**
     * @var Collection<int, Surveillant>
     */
    #[ORM\ManyToMany(targetEntity: Surveillant::class, mappedBy: 'lesEpreuves')]
    private Collection $lesSurveillants;

    public function __construct()
    {
        $this->lesSurveillants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEpreuve(): ?string
    {
        return $this->nomEpreuve;
    }

    public function setNomEpreuve(string $nomEpreuve): static
    {
        $this->nomEpreuve = $nomEpreuve;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDuree(): ?\DateTime
    {
        return $this->duree;
    }

    public function setDuree(\DateTime $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDifficulte(): ?int
    {
        return $this->difficulte;
    }

    public function setDifficulte(int $difficulte): static
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getPointEpreuve(): ?float
    {
        return $this->pointEpreuve;
    }

    public function setPointEpreuve(float $pointEpreuve): static
    {
        $this->pointEpreuve = $pointEpreuve;

        return $this;
    }

    public function getLieuEpreuve(): ?string
    {
        return $this->lieuEpreuve;
    }

    public function setLieuEpreuve(string $lieuEpreuve): static
    {
        $this->lieuEpreuve = $lieuEpreuve;

        return $this;
    }

    public function getTypeEpreuve(): ?string
    {
        return $this->typeEpreuve;
    }

    public function setTypeEpreuve(string $typeEpreuve): static
    {
        $this->typeEpreuve = $typeEpreuve;

        return $this;
    }

    public function getNbIndiceAGagner(): ?int
    {
        return $this->nbIndiceAGagner;
    }

    public function setNbIndiceAGagner(int $nbIndiceAGagner): static
    {
        $this->nbIndiceAGagner = $nbIndiceAGagner;

        return $this;
    }

    public function getDateEpreuveDebut(): ?\DateTime
    {
        return $this->dateEpreuveDebut;
    }

    public function setDateEpreuveDebut(\DateTime $dateEpreuveDebut): static
    {
        $this->dateEpreuveDebut = $dateEpreuveDebut;

        return $this;
    }

    public function getDateEpreuveFin(): ?\DateTime
    {
        return $this->dateEpreuveFin;
    }

    public function setDateEpreuveFin(\DateTime $dateEpreuveFin): static
    {
        $this->dateEpreuveFin = $dateEpreuveFin;

        return $this;
    }

    public function getCoeffAnnee(): ?float
    {
        return $this->coeffAnnee;
    }

    public function setCoeffAnnee(float $coeffAnnee): static
    {
        $this->coeffAnnee = $coeffAnnee;

        return $this;
    }

    public function getLeScore(): ?Score
    {
        return $this->leScore;
    }

    public function setLeScore(?Score $leScore): static
    {
        $this->leScore = $leScore;

        return $this;
    }

    /**
     * @return Collection<int, Surveillant>
     */
    public function getLesSurveillants(): Collection
    {
        return $this->lesSurveillants;
    }

    public function addLesSurveillant(Surveillant $lesSurveillant): static
    {
        if (!$this->lesSurveillants->contains($lesSurveillant)) {
            $this->lesSurveillants->add($lesSurveillant);
            $lesSurveillant->addLesEpreufe($this);
        }

        return $this;
    }

    public function removeLesSurveillant(Surveillant $lesSurveillant): static
    {
        if ($this->lesSurveillants->removeElement($lesSurveillant)) {
            $lesSurveillant->removeLesEpreufe($this);
        }

        return $this;
    }
}
