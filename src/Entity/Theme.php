<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Theme
 */
#[ORM\Table(name: 'theme')]
#[ORM\Entity]
class Theme
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'theme_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $themeId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'libelle', type: 'string', length: 50, nullable: false)]
    private $libelle;

    public function getThemeId(): ?int
    {
        return $this->themeId;
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


}
