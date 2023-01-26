<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{

    #[ORM\Id]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fist_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cv_name = null;

    #[ORM\Column]
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

    public function getFistName(): ?string
    {
        return $this->fist_name;
    }

    public function setFistName(?string $fist_name): self
    {
        $this->fist_name = $fist_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getCvName(): ?string
    {
        return $this->cv_name;
    }

    public function setCvName(?string $cv_name): self
    {
        $this->cv_name = $cv_name;

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
