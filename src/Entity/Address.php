<?php

namespace App\Entity;

use App\Interface\LocationInterface;
use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address implements LocationInterface
{

    public const KEY_NUMBER = 'number';
    public const KEY_STREET_NAME = 'street_name';
    public const KEY_CITY_ID = 'city_id';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    private ?string $street_name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;


    public function __construct(int $number, string $street_name, City $city)
    {
        $this->number = $number;
        $this->street_name = $street_name;
        $this->city = $city;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->street_name;
    }

    public function setStreetName(string $street_name): self
    {
        $this->street_name = $street_name;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getNumber() . ' ' . $this->getStreetName() . ', ' . $this->getCity()->getFullName();
    }

    public function getValueAsArray(array $excepts = [])
    {
        $arr = [
            'id' => $this->getId(),
            self::KEY_NUMBER => $this->getNumber(),
            self::KEY_STREET_NAME => $this->getStreetName(),
            self::KEY_CITY_ID => $this->getCity()->getId(),
            'city_name' => $this->getCity()->getName(),
        ];
        foreach ($excepts as $except)
            unset($arr[$except]);
        return $arr;
    }

    public function getName(): ?string
    {
        return $this->number . ', ' . $this->street_name;
    }

    public function getType(): int
    {
        return Location::getTypeByObj($this);
    }

    public function getTypeString(): string
    {
        return Location::getTypeNameByObj($this);
    }
}
