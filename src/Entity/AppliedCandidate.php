<?php

namespace App\Entity;

use App\Repository\AppliedCandidateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppliedCandidateRepository::class)]
class AppliedCandidate
{

    public const KEY_ID = 'id';
    public const KEY_VALIDATED = 'validated';
    public const KEY_OFFER_ID = 'offer_id';
    public const KEY_CANDIDATE_ID = 'candidate_id';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'applieds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidate $candidate = null;

    #[ORM\Column]
    private ?bool $validated = null;

    #[ORM\ManyToOne(inversedBy: 'applieds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    public function __construct(Candidate $candidate, Offer $offer)
    {
        $this->offer = $offer;
        $this->candidate = $candidate;
        $this->validated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;

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

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getValueAsArray(array $excepts = []): array
    {
        $arr = [
            self::KEY_ID => $this->getId(),
            self::KEY_VALIDATED => $this->isValidated(),
            self::KEY_CANDIDATE_ID => $this->getCandidate()->getId(),
            self::KEY_OFFER_ID => $this->getOffer()->getId(),
        ];
        foreach ($excepts as $except)
            unset($arr[$except]);
        return $arr;
    }

    public function approve(): void
    {
        $this->setValidated(true);
    }
}
