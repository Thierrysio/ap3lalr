<?php

namespace App\Entity;

use App\Repository\PointEpreuveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointEpreuveRepository::class)]
class PointEpreuve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lieuEpreuve = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
