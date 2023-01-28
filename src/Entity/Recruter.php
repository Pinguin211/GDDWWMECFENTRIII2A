<?php

namespace App\Entity;

use App\Repository\RecruterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecruterRepository::class)]
class Recruter
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company_name = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Location $address = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private ?bool $activated = null;

    #[ORM\OneToMany(mappedBy: 'poster', targetEntity: Offer::class, orphanRemoval: true)]
    private Collection $offers;

    public function __construct(User $user, bool $activated = false)
    {
        $this->user = $user;
        $this->activated = $activated;
        $this->offers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
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

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setPoster($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getPoster() === $this) {
                $offer->setPoster(null);
            }
        }

        return $this;
    }
}
