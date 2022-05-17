<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[UniqueEntity('nom')]
#[ORM\HasLifecycleCallbacks]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank()]
    private $nom;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\File(mimeTypes:['image/jpeg', 'image/png'])]
    private $drapeau;

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

    public function getDrapeau(): ?string
    {
        return $this->drapeau;
    }

    public function setDrapeau(string $drapeau): self
    {
        $this->drapeau = $drapeau;

        return $this;
    }

    #[ORM\PostRemove]
    public function deleteDrapeau(): bool
    {
        if (file_exists(__DIR__. "/../../public/assets/img/drapeaux/". $this->drapeau)) {
            unlink(__DIR__. "/../../public/assets/img/drapeaux/". $this->drapeau);
        }
        return true;
    }
}
