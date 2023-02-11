<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class InputOfferInformation extends Constraint
{
    public string $opt;

    public function __construct(mixed $options = null, array $groups = null, mixed $payload = null, string $opt = NULL)
    {
        $this->opt = $opt;
        parent::__construct($options, $groups, $payload);
    }

    public string $badHours = 'Veuillez indiquer un temps horaires entre 1 - 50 heures';

    public string $bad_salary = 'Veuillez indiquer un salaire entre 1 - 999999 euros';

    public string $defaut = 'Valeur incorrect';
}