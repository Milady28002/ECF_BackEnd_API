<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Allergene
 */
#[ORM\Table(name: 'allergene')]
#[ORM\Entity]
class Allergene
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'allergene_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $allergeneId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'libelle', type: 'string', length: 50, nullable: false)]
    private $libelle;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: \Plat::class, mappedBy: 'allergene')]
    private $plat = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plat = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAllergeneId(): ?int
    {
        return $this->allergeneId;
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

    /**
     * @return Collection<int, Plat>
     */
    public function getPlat(): Collection
    {
        return $this->plat;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plat->contains($plat)) {
            $this->plat->add($plat);
            $plat->addAllergene($this);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        if ($this->plat->removeElement($plat)) {
            $plat->removeAllergene($this);
        }

        return $this;
    }

}
