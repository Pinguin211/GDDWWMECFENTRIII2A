<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $post_date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\Column]
    private ?int $week_hours = null;

    #[ORM\Column]
    private ?int $net_salary = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $validated = null;

    #[ORM\Column]
    private ?bool $archived = null;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: AppliedCandidate::class, orphanRemoval: true)]
    private Collection $applieds;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recruter $poster = null;

    public function __construct()
    {
        $this->applieds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPostDate(): ?\DateTimeInterface
    {
        return $this->post_date;
    }

    public function setPostDate(\DateTimeInterface $post_date): self
    {
        $this->post_date = $post_date;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getWeekHours(): ?int
    {
        return $this->week_hours;
    }

    public function setWeekHours(int $week_hours): self
    {
        $this->week_hours = $week_hours;

        return $this;
    }

    public function getNetSalary(): ?int
    {
        return $this->net_salary;
    }

    public function setNetSalary(int $net_salary): self
    {
        $this->net_salary = $net_salary;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return Collection<int, AppliedCandidate>
     */
    public function getApplieds(): Collection
    {
        return $this->applieds;
    }

    public function addApplied(AppliedCandidate $applied): self
    {
        if (!$this->applieds->contains($applied)) {
            $this->applieds->add($applied);
            $applied->setOffer($this);
        }

        return $this;
    }

    public function removeApplied(AppliedCandidate $applied): self
    {
        if ($this->applieds->removeElement($applied)) {
            // set the owning side to null (unless already changed)
            if ($applied->getOffer() === $this) {
                $applied->setOffer(null);
            }
        }

        return $this;
    }

    public function getPoster(): ?Recruter
    {
        return $this->poster;
    }

    public function setPoster(?Recruter $poster): self
    {
        $this->poster = $poster;

        return $this;
    }
}
