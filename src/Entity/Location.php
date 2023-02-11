<?php

namespace App\Entity;

use App\Interface\LocationInterface;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    //Les different type de location
    private const ADDRESS = 1;
    private const CITY = 2;
    private const DEPARTMENT = 3;
    private const REGION = 4;


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column]
    private ?int $type_id = null;

    public function __construct(mixed $obj)
    {
        $type = self::getTypeByClass($obj::class);
        if ($type == 0)
        {
            $this->type = self::REGION;
            $this->type_id = 20;
        }
        else
        {
            $this->type = $type;
            $this->type_id = $obj->getId();
        }
    }

    public static function getTypeByClass(string $class): int
    {
        return match ($class) {
            Address::class => self::ADDRESS,
            City::class => self::CITY,
            Department::class => self::DEPARTMENT,
            Region::class => self::REGION,
            default => 0,
        };
    }

    public static function getClassByType(int $type): string | false
    {
        return match ($type) {
            self::ADDRESS => Address::class,
            self::CITY => City::class,
            self::DEPARTMENT => Department::class,
            self::REGION => Region::class,
            default => false,
        };
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type_id): self
    {
        $this->type_id = $type_id;

        return $this;
    }

    private function getObject(EntityManagerInterface $entityManager): LocationInterface
    {
        $class = self::getClassByType($this->getType());
        if ($class)
            return $entityManager->getRepository($class)->findOneBy(['id'=>$this->getTypeId()]);
        else
            return $entityManager->getRepository(Region::class)->findOneBy(['id'=>20]);
    }

    public function getFullName(EntityManagerInterface $entityManager): string
    {
        return $this->getObject($entityManager)->getFullName();
    }
}
