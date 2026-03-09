<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'menu')]
#[ORM\Entity(repositoryClass: MenuRepository::class)]

class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'menu_id', type: 'integer', nullable: false)]
    private ?int $menuId = null;

    #[ORM\Column(name: 'titre', type: 'string', length: 50, nullable: false)]
    private ?string $titre = null;

    #[ORM\Column(name: 'nombre_personne_minimum', type: 'integer', nullable: false)]
    private ?int $nombrePersonneMinimum = null;

    #[ORM\Column(name: 'prix_par_personne', type: 'float', precision: 10, scale: 0, nullable: false)]
    private ?float $prixParPersonne = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    private ?string $description = null;

    #[ORM\Column(name: 'quantite_restante', type: 'integer', nullable: false)]
    private ?int $quantiteRestante = null;

    #[ORM\ManyToOne(targetEntity: Regime::class)]
    #[ORM\JoinColumn(name: 'regime_id', referencedColumnName: 'regime_id', nullable: false)]
    private ?Regime $regime = null;

    #[ORM\ManyToOne(targetEntity: Theme::class)]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'theme_id', nullable: false)]
    private ?Theme $theme = null;

    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'menu')]
    private Collection $commande;

    #[ORM\JoinTable(name: 'menu_plat')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')]
    #[ORM\InverseJoinColumn(name: 'plat_id', referencedColumnName: 'plat_id')]
    #[ORM\ManyToMany(targetEntity: Plat::class, inversedBy: 'menu')]
    private Collection $plat;

    public function __construct()
    {
        $this->commande = new ArrayCollection();
        $this->plat = new ArrayCollection();
    }

    public function getMenuId(): ?int
    {
        return $this->menuId;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getNombrePersonneMinimum(): ?int
    {
        return $this->nombrePersonneMinimum;
    }

    public function setNombrePersonneMinimum(int $nombrePersonneMinimum): static
    {
        $this->nombrePersonneMinimum = $nombrePersonneMinimum;
        return $this;
    }

    public function getPrixParPersonne(): ?float
    {
        return $this->prixParPersonne;
    }

    public function setPrixParPersonne(float $prixParPersonne): static
    {
        $this->prixParPersonne = $prixParPersonne;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getQuantiteRestante(): ?int
    {
        return $this->quantiteRestante;
    }

    public function setQuantiteRestante(int $quantiteRestante): static
    {
        $this->quantiteRestante = $quantiteRestante;
        return $this;
    }

    public function getRegime(): ?Regime
    {
        return $this->regime;
    }

    public function setRegime(?Regime $regime): static
    {
        $this->regime = $regime;
        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getCommande(): Collection
    {
        return $this->commande;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commande->contains($commande)) {
            $this->commande->add($commande);
            $commande->addMenu($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commande->removeElement($commande)) {
            $commande->removeMenu($this);
        }

        return $this;
    }

    public function getPlat(): Collection
    {
        return $this->plat;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plat->contains($plat)) {
            $this->plat->add($plat);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        $this->plat->removeElement($plat);
        return $this;
    }
}