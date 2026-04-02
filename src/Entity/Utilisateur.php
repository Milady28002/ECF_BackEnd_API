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


    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name = null;

   
    #[ORM\Column(name: 'firstname', type: 'string', length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'api_token', type: 'string', length: 255, nullable: true)]
    private ?string $apiToken = null;

    #[ORM\Column(name: 'reset_token', type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(name: 'reset_token_expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;

        return $this;
    }

    #[ORM\Column(name: 'telephone', type: 'string', length: 50, nullable: false)]
    private ?string $telephone = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 50, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: 'pays', type: 'string', length: 50, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(name: 'adresse_postale', type: 'string', length: 50, nullable: true)]
    private ?string $adressePostale = null;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\JoinTable(name: 'utilisateur_role')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateur')]
    private Collection $role;

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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }


    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(?string $adressePostale): static
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

        
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
        
    }
    public function getUsername(): string
    {
   
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
