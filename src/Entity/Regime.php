<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Regime
 */
#[ORM\Table(name: 'regime')]
#[ORM\Entity]
class Regime
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'regime_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $regimeId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'libelle', type: 'string', length: 50, nullable: false)]
    private $libelle;

    public function getRegimeId(): ?int
    {
        return $this->regimeId;
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


}
