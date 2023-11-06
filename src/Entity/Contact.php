<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Vous devez entrer votre nom et prénom')]
    private ?string $fullname = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: '{{ value }} n\'est pas une adresse mail valide')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Vous devez entrer le nom de votre projet')]
    private ?string $projectName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Ajouter une description pour votre projet')]
    #[Assert\Length(min: 30, minMessage: 'La description du projet doit contenir au moins {{ limit }} caractères')]
    private ?string $projectDescription = null;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: FilesContact::class, cascade: ['persist'])]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): self
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getProjectDescription(): ?string
    {
        return $this->projectDescription;
    }

    public function setProjectDescription(string $projectDescription): self
    {
        $this->projectDescription = $projectDescription;

        return $this;
    }

    /**
     * @return Collection<int, FilesContact>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(FilesContact $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setContact($this);
        }

        return $this;
    }

    public function removeFile(FilesContact $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getContact() === $this) {
                $file->setContact(null);
            }
        }

        return $this;
    }
}
