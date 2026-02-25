<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Avis
 */
#[ORM\Table(name: 'avis')]
#[ORM\Index(name: 'fk_avis_utilisateur', columns: ['utilisateur_id'])]
#[ORM\Entity]
class Avis
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'avis_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $avisId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'note', type: 'string', length: 50, nullable: false)]
    private $note;

    /**
     * @var string
     */
    #[ORM\Column(name: 'description', type: 'string', length: 50, nullable: false)]
    private $description;

    /**
     * @var string
     */
    #[ORM\Column(name: 'statut', type: 'string', length: 50, nullable: false)]
    private $statut;

    /**
     * @var \Utilisateur
     */
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')]
    #[ORM\ManyToOne(targetEntity: \Utilisateur::class)]
    private $utilisateur;

    public function getAvisId(): ?int
    {
        return $this->avisId;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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


}
