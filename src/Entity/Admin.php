<?php

namespace App\Entity;

class Admin
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
