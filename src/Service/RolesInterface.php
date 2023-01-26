<?php

namespace App\Service;

use App\Entity\User;

class RolesInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_CONSULTANT = 'ROLE_CONSULTANT';
    public const ROLE_RECRUTER = 'ROLE_RECRUTER';
    public const ROLE_CANDIDATE = 'ROLE_CANDIDATE';

    public function is_recruter(User $user): bool
    {
        return in_array(self::ROLE_RECRUTER, $user->getRoles());
    }

    public function is_consultant(User $user): bool
    {
        return in_array(self::ROLE_CONSULTANT, $user->getRoles());
    }

    public function is_candidate(User $user): bool
    {
        return in_array(self::ROLE_CANDIDATE, $user->getRoles());
    }

    public function is_admin(User $user): bool
    {
        return in_array(self::ROLE_ADMIN, $user->getRoles());
    }
}