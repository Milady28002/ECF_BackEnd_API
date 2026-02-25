<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Plat
 */
#[ORM\Table(name: 'plat')]
#[ORM\Entity]
class Plat
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'plat_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $platId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'titre_plat', type: 'string', length: 50, nullable: false)]
    private $titrePlat;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'photo', type: 'blob', length: 65535, nullable: true, options: ['default' => 'NULL'])]
    private $photo = 'NULL';

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: \Menu::class, mappedBy: 'plat')]
    private $menu = array();

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\JoinTable(name: 'plat_allergene')]
    #[ORM\JoinColumn(name: 'plat_id', referencedColumnName: 'plat_id')]
    #[ORM\InverseJoinColumn(name: 'allergene_id', referencedColumnName: 'allergene_id')]
    #[ORM\ManyToMany(targetEntity: \Allergene::class, inversedBy: 'plat')]
    private $allergene = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menu = new \Doctrine\Common\Collections\ArrayCollection();
        $this->allergene = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPlatId(): ?int
    {
        return $this->platId;
    }

    public function getTitrePlat(): ?string
    {
        return $this->titrePlat;
    }

    public function setTitrePlat(string $titrePlat): static
    {
        $this->titrePlat = $titrePlat;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenu(): Collection
    {
        return $this->menu;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menu->contains($menu)) {
            $this->menu->add($menu);
            $menu->addPlat($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menu->removeElement($menu)) {
            $menu->removePlat($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Allergene>
     */
    public function getAllergene(): Collection
    {
        return $this->allergene;
    }

    public function addAllergene(Allergene $allergene): static
    {
        if (!$this->allergene->contains($allergene)) {
            $this->allergene->add($allergene);
        }

        return $this;
    }

    public function removeAllergene(Allergene $allergene): static
    {
        $this->allergene->removeElement($allergene);

        return $this;
    }

}
