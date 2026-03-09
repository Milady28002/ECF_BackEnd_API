<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;


/**
 * Role
 */
#[ORM\Table(name: 'role')]
#[ORM\Entity]
class Role
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'role_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $roleId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'libelle', type: 'string', length: 50, nullable: false)]
    private $libelle;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'role')]
    private $utilisateur = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->utilisateur = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
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
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateur(): Collection
    {
        return $this->utilisateur;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateur->contains($utilisateur)) {
            $this->utilisateur->add($utilisateur);
            $utilisateur->addRole($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateur->removeElement($utilisateur)) {
            $utilisateur->removeRole($this);
        }

        return $this;
    }

}
