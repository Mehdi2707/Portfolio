<?php

namespace App\Entity;

use App\Repository\AlertsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlertsRepository::class)]
class Alerts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $link = null;

    #[ORM\Column]
    private ?bool $isClosed = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    #[ORM\Column]
    private ?bool $isAccessible = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $html = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function isIsClosed(): ?bool
    {
        return $this->isClosed;
    }

    public function setIsClosed(bool $isClosed): self
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function isIsAccessible(): ?bool
    {
        return $this->isAccessible;
    }

    public function setIsAccessible(bool $isAccessible): self
    {
        $this->isAccessible = $isAccessible;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): self
    {
        $this->html = $html;

        return $this;
    }
}
