<?php
namespace App\Entity;

use App\Repository\MoyenDePaiementRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User; // <— Assure-toi que ce namespace est correct

#[ORM\Entity(repositoryClass: MoyenDePaiementRepository::class)]
class MoyenDePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    // --- AJOUT de la relation vers User ---
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    // ---------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    // --- Getter/Setter pour la relation user ---
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    // --------------------------------------------
}
