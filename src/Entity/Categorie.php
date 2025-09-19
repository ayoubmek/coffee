<?php
// src/Entity/Categorie.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: "App\Repository\CategorieRepository")]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icone = null;   // <- stores "assets/media/ch/filename.ext"

    #[ORM\OneToMany(mappedBy: "categorie", targetEntity: Produit::class, cascade: ["persist", "remove"])]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    /* ---------- getters / setters ---------- */
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getIcone(): ?string { return $this->icone; }
    public function setIcone(?string $icone): self { $this->icone = $icone; return $this; }

    /** @return Collection|Produit[] */
    public function getProduits(): Collection { return $this->produits; }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setCategorie($this);
        }
        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }
        return $this;
    }

    /* -------------- unmapped upload -------------- */
    private ?File $iconeFile = null;

    public function getIconeFile(): ?File { return $this->iconeFile; }

    public function setIconeFile(?File $iconeFile): self
    {
        $this->iconeFile = $iconeFile;
        if ($iconeFile instanceof UploadedFile) {
            // force Doctrine to see a change (lifecycle will handle move)
            $this->icone = null;
        }
        return $this;
    }
}