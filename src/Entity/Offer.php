<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use App\Validator\InputOfferInformation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    public const KEY_ID = 'id';
    public const KEY_TITLE = 'title';
    public const KEY_LOCATION_ID = 'location_id';
    public const KEY_POST_DATE = 'post_date';
    public const KEY_WEEK_HOURS = 'week_hours';
    public const KEY_NET_SALARY = 'net_salary';
    public const KEY_DESCRIPTION = 'description';
    public const KEY_ARCHIVED = 'archived';
    public const KEY_VALIDATED = 'validated';
    public const KEY_APPLIEDS_ID = 'appplieds_id';
    public const KEY_POSTER_ID = 'poster_id';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[NotBlank]
    #[Length(max: 255, maxMessage: "Le titre doit contenir au maximum 255 caractères")]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $post_date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[NotBlank]
    #[InputOfferInformation(opt: 'hours')]
    #[ORM\Column]
    private ?int $week_hours = null;

    #[NotBlank]
    #[InputOfferInformation(opt: 'salary')]
    #[ORM\Column]
    private ?int $net_salary = null;

    #[NotBlank]
    #[Length(min: 50, max: 4000, minMessage: "La description doit contenir au minimum 50 caractères",
        maxMessage: "La description doit contenir au maximum 4000 caractères")]
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

    public function getAppliedsIdList(): array
    {
        $res = [];
        $arr = $this->getApplieds();
        foreach ($arr as $applied)
            $res[] = $applied->getId();
        return $res;
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

    public function make_archived(EntityManagerInterface $entityManager): void
    {
        $this->setArchived(true);
        $entityManager->flush();
    }

    public function make_unarchived(EntityManagerInterface $entityManager): void
    {
        $this->setArchived(false);
        $entityManager->flush();
    }

    public function make_delete(EntityManagerInterface $entityManager): void
    {
        $applieds = $this->getApplieds();
        foreach ($applieds as $applied)
            $entityManager->remove($applied);
        $entityManager->remove($this->getLocation());
        $entityManager->remove($this);
        $entityManager->flush();
    }

    public function getValueAsArray(array $excepts = []): array
    {
        $arr = [
            self::KEY_ID => $this->getId(),
            self::KEY_TITLE => $this->getTitle(),
            self::KEY_NET_SALARY => $this->getNetSalary(),
            self::KEY_WEEK_HOURS => $this->getWeekHours(),
            self::KEY_DESCRIPTION => $this->getDescription(),
            self::KEY_POST_DATE => $this->getPostDate()->format("d/m/Y - H:i:s"),
            self::KEY_ARCHIVED => $this->isArchived(),
            self::KEY_VALIDATED => $this->isValidated(),
            self::KEY_POSTER_ID => $this->getPoster()->getId(),
            self::KEY_LOCATION_ID => $this->getLocation()->getId(),
            self::KEY_APPLIEDS_ID => $this->getAppliedsIdList()
        ];
        foreach ($excepts as $except)
            unset($arr[$except]);
        return $arr;
    }

    public function appliedThisOffer(Candidate $candidate, EntityManagerInterface $entityManager)
    {
        $applied = new AppliedCandidate($candidate, $this);
        $entityManager->persist($applied);
        $entityManager->flush();
    }

    public function candidateAlreadyApplied(Candidate $candidate): bool
    {
        $applieds = $this->getApplieds();
        foreach ($applieds as $applied)
        {
            if ($applied->getCandidate()->getId() === $candidate->getId())
                return true;
        }
        return false;
    }

    public function getCountValidateApplieds(): int
    {
        $nb = 0;
        $applieds = $this->getApplieds();
        foreach ($applieds as $applied)
        {
            if ($applied->isValidated())
                $nb++;
        }
        return $nb;
    }

    public function approve(): void
    {
        $this->setValidated(true);
    }
}
