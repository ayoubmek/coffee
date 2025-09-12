<?php 
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Categorie;

#[ORM\Entity(repositoryClass: "App\Repository\ProduitRepository")]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;


    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;


    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: 'float')]
    private ?float $prix = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prixAncien = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $reduction = null;

    // --- Relation ManyToOne vers Categorie ---
    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "produits")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    // --- Getters / Setters ---
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }


        public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }
    public function getImage(): ?string { return $this->image; }
    public function setImage(string $image): static { $this->image = $image; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

    public function getPrixAncien(): ?float { return $this->prixAncien; }
    public function setPrixAncien(?float $prixAncien): static { $this->prixAncien = $prixAncien; return $this; }

    public function getReduction(): ?int { return $this->reduction; }
    public function setReduction(?int $reduction): static { $this->reduction = $reduction; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static { $this->categorie = $categorie; return $this; }
}
