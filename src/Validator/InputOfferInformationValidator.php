<?php

namespace App\Validator;

use Exception;
use PDO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InputOfferInformationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\InputOfferInformation */
        if (null === $value || '' === $value) {
            return;
        }
        if ($constraint->opt === 'salary')
        {
            if ($value <= 0 || $value > 999999 )
                $this->context->buildViolation($constraint->bad_salary)->addViolation();
        }
        elseif ($constraint->opt === 'hours')
        {
            if ($value <= 0 || $value > 50)
                $this->context->buildViolation($constraint->badHours)->addViolation();
        }
        else
        {
            if ($value < 1)
                $this->context->buildViolation($constraint->defaut)->addViolation();
        }

    }
}