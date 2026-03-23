<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlatRepository;
use App\Entity\Menu;
use App\Entity\Allergene;

#[ORM\Table(name: 'plat')]
#[ORM\Entity(repositoryClass: PlatRepository::class)]
class Plat
{
    #[ORM\Column(name: 'plat_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $platId = null;

    #[ORM\Column(name: 'type_plat', type: 'string', length: 20)]
    private ?string $typePlat = null;

    #[ORM\Column(name: 'titre_plat', type: 'string', length: 50, nullable: false)]
    private ?string $titrePlat = null;

    #[ORM\Column(name: 'image_url', type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'plat')]
    private Collection $menu;

    #[ORM\JoinTable(name: 'plat_allergene')]
    #[ORM\JoinColumn(name: 'plat_id', referencedColumnName: 'plat_id')]
    #[ORM\InverseJoinColumn(name: 'allergene_id', referencedColumnName: 'allergene_id')]
    #[ORM\ManyToMany(targetEntity: Allergene::class, inversedBy: 'plat')]
    private Collection $allergene;

    public function __construct()
    {
        $this->menu = new \Doctrine\Common\Collections\ArrayCollection();
        $this->allergene = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPlatId(): ?int
    {
        return $this->platId;
    }

    public function getTypePlat(): ?string
    {
        return $this->typePlat;
    }

    public function setTypePlat(string $typePlat): static
    {
        $this->typePlat = $typePlat;
        return $this;
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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
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