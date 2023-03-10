<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use App\Service\PathInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{

    public const KEY_USER_ID = 'user_id';
    public const KEY_ID = 'id';
    public const KEY_FIRST_NAME = 'first_name';
    public const KEY_LAST_NAME = 'last_name';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(nullable: true)]
    private ?int $cv_id = null;

    #[ORM\Column]
    private ?bool $activated = null;

    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: AppliedCandidate::class, orphanRemoval: true)]
    private Collection $applieds;

    public function __construct(User $user, bool $activated = false)
    {
        $this->user = $user;
        $this->activated = $activated;
        $this->applieds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

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

    public function getCvId(): ?string
    {
        return $this->cv_id;
    }

    public function setCvId(?string $cv_id): self
    {
        $this->cv_id = $cv_id;

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
            $applied->setCandidate($this);
        }

        return $this;
    }

    public function removeApplied(AppliedCandidate $applied): self
    {
        if ($this->applieds->removeElement($applied)) {
            // set the owning side to null (unless already changed)
            if ($applied->getCandidate() === $this) {
                $applied->setCandidate(null);
            }
        }

        return $this;
    }

    public function getValueAsArray(array $excepts = []): array
    {
        $arr = [
            self::KEY_ID => $this->getId(),
            self::KEY_USER_ID => $this->getUser()->getId(),
            self::KEY_FIRST_NAME => $this->getFirstName(),
            self::KEY_LAST_NAME => $this->getLastName(),
            'cv_id' => $this->getCvId(),
            'activated' => $this->isActivated(),
        ];
        foreach ($excepts as $except)
            unset($arr[$except]);
        return $arr;
    }

    public function getCvPath(): string | false
    {
        if (($cv_id = $this->getCvId()))
            return '/cv/' . $cv_id . '.pdf';
        else
            return false;
    }

    public function getAbsoluteCvPath(PathInterface $path): string | false
    {
        if ($this->getCvId())
        {
            $cv = $path->getUserCvDirPath() . $this->getCvId() . '.pdf';
            return file_exists($cv) ? $cv : false;
        }
        else
            return false;
    }

    public function approve(): void
    {
        $this->setActivated(true);
    }

    public function getFullName(): string
    {
        return $this->getLastName() . ' ' . $this->getFirstName();
    }
}
