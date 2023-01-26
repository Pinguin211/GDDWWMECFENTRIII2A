<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UserEmailExist extends Constraint
{
    public string $message = 'Cette adresse email est deja utiliser par un autre utilisateur';

    public string $error_bdd = 'Problème de connexion';
}