<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Commande
 */
#[ORM\Table(name: 'commande')]
#[ORM\Index(name: 'utilisateur_id', columns: ['utilisateur_id'])]
#[ORM\Entity]
class Commande
{
    /**
     * @var string
     */
    #[ORM\Column(name: 'numero_commande', type: 'string', length: 50, nullable: false)]
    #[ORM\Id]
    private ?string $numeroCommande = null;
    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date_commande', type: 'date', nullable: false)]
    private $dateCommande;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'date_prestation', type: 'date', nullable: false)]
    private $datePrestation;

    /**
     * @var string
     */
    #[ORM\Column(name: 'heure_livraison', type: 'string', length: 50, nullable: false)]
    private $heureLivraison;

    #[ORM\Column(name: 'adresse_livraison', type: 'string', length: 255, nullable: false)]
    private ?string $adresseLivraison = null;

    /**
     * @var float
     */
    #[ORM\Column(name: 'prix_menu', type: 'float', precision: 10, scale: 0, nullable: false)]
    private $prixMenu;

    /**
     * @var float
     */
    #[ORM\Column(name: 'prix_livraison', type: 'float', precision: 10, scale: 0, nullable: false)]
    private $prixLivraison;

    /**
     * @var int
     */
    #[ORM\Column(name: 'nombre_personnes', type: 'integer', nullable: false)]
    private $nombrePersonnes;

    /**
     * @var string
     */
    #[ORM\Column(name: 'statut', type: 'string', length: 50, nullable: false)]
    private $statut;

    #[ORM\Column(name: 'motif_annulation', type: 'text', nullable: true)]
    private ?string $motifAnnulation = null;

    #[ORM\Column(name: 'mode_contact_annulation', type: 'string', length: 50, nullable: true)]
    private ?string $modeContactAnnulation = null;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'pret_materiel', type: 'boolean', nullable: false)]
    private $pretMateriel;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'restitution_materiel', type: 'boolean', nullable: false)]
    private $restitutionMateriel;

    /**
     * @var \Utilisateur
     */
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')]
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    private $utilisateur;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\JoinTable(name: 'commande_menu')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'numero_commande')]
    #[ORM\InverseJoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')]
    #[ORM\ManyToMany(targetEntity: Menu::class, inversedBy: 'commande')]
    private $menu = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menu = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDatePrestation(): ?\DateTimeInterface
    {
        return $this->datePrestation;
    }

    public function setDatePrestation(\DateTimeInterface $datePrestation): static
    {
        $this->datePrestation = $datePrestation;

        return $this;
    }

    public function getHeureLivraison(): ?string
    {
        return $this->heureLivraison;
    }

    public function setHeureLivraison(string $heureLivraison): static
    {
        $this->heureLivraison = $heureLivraison;

        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(string $adresseLivraison): static
    {
    $this->adresseLivraison = $adresseLivraison;

    return $this;
    }

    public function getPrixMenu(): ?float
    {
    return $this->prixMenu;
    }

    public function setPrixMenu(float $prixMenu): static
    {
        $this->prixMenu = $prixMenu;

        return $this;
    }

    public function getPrixLivraison(): ?float
    {
        return $this->prixLivraison;
    }

    public function setPrixLivraison(float $prixLivraison): static
    {
        $this->prixLivraison = $prixLivraison;

        return $this;
    }

    public function getNombrePersonnes(): ?int
    {
        return $this->nombrePersonnes;
    }

    public function setNombrePersonnes(int $nombrePersonnes): static
    {
        $this->nombrePersonnes = $nombrePersonnes;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function getModeContactAnnulation(): ?string
    {
        return $this->modeContactAnnulation;
    }

    public function setModeContactAnnulation(?string $modeContactAnnulation): static
    {
        $this->modeContactAnnulation = $modeContactAnnulation;

        return $this;
    }

    public function isPretMateriel(): ?bool
    {
        return $this->pretMateriel;
    }

    public function setPretMateriel(bool $pretMateriel): static
    {
        $this->pretMateriel = $pretMateriel;

        return $this;
    }

    public function isRestitutionMateriel(): ?bool
    {
        return $this->restitutionMateriel;
    }

    public function setRestitutionMateriel(bool $restitutionMateriel): static
    {
        $this->restitutionMateriel = $restitutionMateriel;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

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
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        $this->menu->removeElement($menu);

        return $this;
    }



}
