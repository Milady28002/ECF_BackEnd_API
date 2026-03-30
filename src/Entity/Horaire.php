<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
#[ORM\Table(name: 'horaire')]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'horaire_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'jour', length: 50)]
    private ?string $jour = null;

    #[ORM\Column(name: 'heure_ouverture', length: 50)]
    private ?string $heureOuverture = null;

    #[ORM\Column(name: 'heure_fermeture', length: 50)]
    private ?string $heureFermeture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;
        return $this;
    }

    public function getHeureOuverture(): ?string
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(string $heureOuverture): static
    {
        $this->heureOuverture = $heureOuverture;
        return $this;
    }

    public function getHeureFermeture(): ?string
    {
        return $this->heureFermeture;
    }

    public function setHeureFermeture(string $heureFermeture): static
    {
        $this->heureFermeture = $heureFermeture;
        return $this;
    }
}