<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Utilisateur
 */
#[ORM\Table(name: 'utilisateur')]
#[ORM\Entity]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'utilisateur_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $utilisateurId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'firstname', type: 'string', length: 255, nullable: true, options: ['default' => 'NULL'])]
    private ?string $firstname = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false)]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private $password;

    /**
     * @var string
     */
    #[ORM\Column(name: 'telephone', type: 'string', length: 50, nullable: false)]
    private $telephone;

    /**
     * @var string
     */
    #[ORM\Column(name: 'ville', type: 'string', length: 50, nullable: false)]
    private $ville;

    /**
     * @var string
     */
    #[ORM\Column(name: 'pays', type: 'string', length: 50, nullable: false)]
    private $pays;

    /**
     * @var string
     */
    #[ORM\Column(name: 'adresse_postale', type: 'string', length: 50, nullable: false)]
    private $adressePostale;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\JoinTable(name: 'utilisateur_role')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateur')]
    private $role = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->role = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(string $adressePostale): static
    {
        $this->adressePostale = $adressePostale;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRole(): Collection
    {
        return $this->role;
    }

    public function addRole(Role $role): static
    {
        if (!$this->role->contains($role)) {
            $this->role->add($role);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->role->removeElement($role);

        return $this;
    }
    public function getUserIdentifier(): string
    {
    return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->role as $roleEntity) {
            $libelle = (string) $roleEntity->getLibelle();

            $libelle = strtoupper($libelle);
            if (!str_starts_with($libelle, 'ROLE_')) {
                $libelle = 'ROLE_' . $libelle;
            }

            $roles[] = $libelle;
        }

        // Garantie minimale
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
        
    }
    public function getUsername(): string
    {
    // Compat ancien Symfony / vieux composants
    return $this->getUserIdentifier();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {    
    }
}
