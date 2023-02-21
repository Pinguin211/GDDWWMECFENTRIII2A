<?php

if (!isset($argv[1]) || !preg_match('^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*^', $argv[1]))
    die("\nphp source/create_admin.php \"mot_de_passe\"\n\nle mot de passe doit contenir minimum 8 caracteres\net le mot de passe doit contenir au minimum 1 minuscule, 1 majuscule et 1 chiffre\n\n");
else
{
    $out = '';
    $code = 0;
    exec("php bin/console security:hash-password 123AZEqsd", $out, $code);
    if ($code === 0)
        $password = explode(' ', trim(preg_replace('/\s\s+/', ' ', $out[4])))[2];
    else
        die("\nImpossible de hasher le mot de passe\n\n");
    $password = str_replace('$', '\$', $password);
    $query = 'insert into user(email, password, roles) values (\'admin@admin\', \''.$password.'\', \'[\"ROLE_ADMIN\"]\')';
    exec("php bin/console dbal:run-sql \"$query\"", $out, $code);
    if ($code === 0)
        die("\n SUCCESS !! L'admin à bien était enregistré\n\n");
    else
        die("\nERREUR : $out\n\n");
}