<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu
 */
#[ORM\Table(name: 'menu')]
#[ORM\Entity]
class Menu
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'menu_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $menuId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'titre', type: 'string', length: 50, nullable: false)]
    private $titre;

    /**
     * @var int
     */
    #[ORM\Column(name: 'nombre_personne_minimum', type: 'integer', nullable: false)]
    private $nombrePersonneMinimum;

    /**
     * @var float
     */
    #[ORM\Column(name: 'prix_par_personne', type: 'float', precision: 10, scale: 0, nullable: false)]
    private $prixParPersonne;

    /**
     * @var string
     */
    #[ORM\Column(name: 'regime', type: 'string', length: 50, nullable: false)]
    private $regime;

    /**
     * @var string
     */
    #[ORM\Column(name: 'description', type: 'string', length: 50, nullable: false)]
    private $description;

    /**
     * @var int
     */
    #[ORM\Column(name: 'quantite_restante', type: 'integer', nullable: false)]
    private $quantiteRestante;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: \Commande::class, mappedBy: 'menu')]
    private $commande = array();

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\JoinTable(name: 'menu_plat')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')]
    #[ORM\InverseJoinColumn(name: 'plat_id', referencedColumnName: 'plat_id')]
    #[ORM\ManyToMany(targetEntity: \Plat::class, inversedBy: 'menu')]
    private $plat = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commande = new \Doctrine\Common\Collections\ArrayCollection();
        $this->plat = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getRegime(): ?string
    {
        return $this->regime;
    }

    public function setRegime(string $regime): static
    {
        $this->regime = $regime;

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

    /**
     * @return Collection<int, Commande>
     */
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
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        $this->plat->removeElement($plat);

        return $this;
    }

}
