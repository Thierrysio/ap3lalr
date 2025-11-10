<?php

namespace App\Entity;

use App\Repository\CapitaineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapitaineRepository::class)]
class Capitaine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nbJoker = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbJoker(): ?int
    {
        return $this->nbJoker;
    }

    public function setNbJoker(int $nbJoker): static
    {
        $this->nbJoker = $nbJoker;

        return $this;
    }
}
