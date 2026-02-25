<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Horaire
 */
#[ORM\Table(name: 'horaire')]
#[ORM\Entity]
class Horaire
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'horaire_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $horaireId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'jour', type: 'string', length: 50, nullable: false)]
    private $jour;

    /**
     * @var string
     */
    #[ORM\Column(name: 'heure_ouverture', type: 'string', length: 50, nullable: false)]
    private $heureOuverture;

    /**
     * @var string
     */
    #[ORM\Column(name: 'heure_fermeture', type: 'string', length: 50, nullable: false)]
    private $heureFermeture;

    public function getHoraireId(): ?int
    {
        return $this->horaireId;
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
