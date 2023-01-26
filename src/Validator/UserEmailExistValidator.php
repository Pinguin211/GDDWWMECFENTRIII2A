<?php

namespace App\Validator;

use Exception;
use PDO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserEmailExistValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\UserEmailExist */
        // On laisse NotBlank, NotNull, etc...s'occuper de valider ce type d'erreur
        if (null === $value || '' === $value) {
            return;
        }
        try {
            $pdo = new PDO($_ENV['DATABASE_PDO_URL'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
            $stat = $pdo->prepare("SELECT email FROM user WHERE email = ?");
            $stat->bindValue(1, $value);
            if (!$stat->execute())
                throw new Exception("Request Error");
            $res = $stat->fetchAll();
        } catch (Exception $exception) {
            $this->context->buildViolation($constraint->error_bdd)->addViolation();
        }
        if (!empty($res))
            $this->context->buildViolation($constraint->message)->addViolation();
    }
}