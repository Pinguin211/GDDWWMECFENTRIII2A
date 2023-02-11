<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Service\RolesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Validator\UserEmailExist;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[NotBlank]
    #[Length(max: 255, maxMessage: "L'email doit contenir au maximum 255 caractères")]
    #[Email(message: "L'adresse email n'est pas correct", mode: 'loose')]
    #[UserEmailExist]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[NotBlank]
    #[Length(min: 8, max: 32,
    minMessage: "Le mot de passe doit contenir au minimum 8 caractères",
    maxMessage: "Le mot de passe doit contenir au maximum 32 caractères"
    )]
    #[Regex(pattern: '^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*^',
    message: 'Le mot de passe doit contenir au minimum 1 minuscule, 1 majuscule et 1 chiffre'
    )]
    #[ORM\Column]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function haveRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    private function getExtendedRoles(string $class, string $role, EntityManagerInterface $entityManager): mixed
    {
        if (!$this->haveRole($role))
            return false;
        return $entityManager->getRepository($class)->findOneBy(['user'=>$this]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return Recruter|false|NULL - NULL si n'a pas de class recruter, false si n'a pas les droits requis
     */
    public function getRecruter(EntityManagerInterface $entityManager): Recruter | NULL | false
    {
        return $this->getExtendedRoles(Recruter::class, RolesInterface::ROLE_RECRUTER, $entityManager);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return Recruter|false|NULL - NULL si n'a pas de class recruter, false si n'a pas les droits requis
     */
    public function getCandidate(EntityManagerInterface $entityManager): Candidate | NULL | false
    {
        return $this->getExtendedRoles(Candidate::class, RolesInterface::ROLE_CANDIDATE, $entityManager);
    }

    public function getAdmin(): Admin | false
    {
        return $this->haveRole(RolesInterface::ROLE_ADMIN) ? new Admin($this) : false;
    }

    public function getConsultant(): Consultant | false
    {
        return $this->haveRole(RolesInterface::ROLE_CONSULTANT) ? new Consultant($this) : false;
    }

    public function getValueAsArray(array $excepts = []): array
    {
        $arr = [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
        ];
        foreach ($excepts as $except)
            unset($arr[$except]);
        return $arr;
    }
}
