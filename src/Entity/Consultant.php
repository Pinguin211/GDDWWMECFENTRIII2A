<?php

namespace App\Entity;

class Consultant
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
