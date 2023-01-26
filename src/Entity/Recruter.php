<?php

namespace App\Entity;

use App\Repository\RecruterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecruterRepository::class)]
class Recruter
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'recruter', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company_name = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Location $address = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private ?bool $activated = null;

    public function __construct(User $user, bool $activated = false)
    {
        $this->user = $user;
        $this->activated = $activated;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(?string $company_name): self
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function getAddress(): ?Location
    {
        return $this->address;
    }

    public function setAddress(?Location $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function isActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }
}
