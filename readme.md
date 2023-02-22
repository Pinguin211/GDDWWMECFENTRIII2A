# GDDWWMECFENTRIII2A

ÉVALUATION D’ENTRAÎNEMENT EN COURS DE FORMATION :  
Développer la partie back-end d’une application web

Lien github du projet: https://github.com/Pinguin211/GDDWWMECFENTRIII2A.git

## Pour commencer

Les instructions sont pour un serveur sous linux Ubuntu (LAMP)  
Vous aurez aussi besoin des privilege de super-utilisateurs sur cette machine

### Pré-requis

Afin de pouvoir exécuter l'application sur votre poste, vous devez d'abord installer les dépendances suivantes :
* Apache2
* Php8.1
* MySql (Si la base de données est en local)
* NodeJs
* composer
* git
* Symfony-cli

### Installation
Ci-dessous les liens et tutoriels d'installation pour les prérequis

#### Apache2 - https://doc.ubuntu-fr.org/apache2

#### Php (Version 8 minimum) - https://doc.ubuntu-fr.org/php

#### MySql - https://doc.ubuntu-fr.org/mysql

#### Nodejs - https://doc.ubuntu-fr.org/nodejs

#### Composer - https://doc.ubuntu-fr.org/composer

#### Git - https://doc.ubuntu-fr.org/git

#### Symfony-Cli - https://symfony.com/download

---

## Démarrage

Pour chaque étape il y aura les commandes associées

### Clonage du projet

Sur votre machine dirigée vous vers le dossier `/var/www`

    cd /var/www

Puis cloner le projet à partir de son url

    sudo git clone https://github.com/Pinguin211/GDDWWMECFENTRIII2A.git

Placer vous ensuite dans le dossier du projet

    cd GDDWWMECFENTRIII2A

### Installation des bibliothèques du projet

Une fois dans le dossier du projet (vous devriez voir sa) :

# PHOTO

Exécuté la commande suivante pour installer les dépendances au projet

    sudo composer install --no-dev --optimize-autoloader

Puis effectuer la commande :

    sudo symfony check:requirements

Cette commande fera la verification des bibliothèques du projet, vous devriez voie sa :

# PHOTO

### Configuration du projet

Pour commencer vous exécuterez la commande suivante pour passer les paramètres du projet en mode production

    sudo APP_ENV=prod APP_DEBUG=0

Puis la commande suivante pour créer le dossier de logs

    sudo mkdir var/log && chmod 777 var/log

Vous devrez ensuite configurer la base de données, verifier que votre base de données soit bien activé,
vous effectuerez la commande suivante pour lancer le script

    sudo php sources/set_data_base.php

Vous devez ensuite remplir les informations concernant votre base de données

# PHOTO

Limage ci-dessus montre les informations pour une base en local vos informations
seront susceptible de changer selon vos besoins

Ensuite vous effectuerez la migration pour créer les tables nécessaires à la base de données  
**Votre base de données doit être vide pour effectuer ce script**

    sudo php bin/console doctrine:migration:migrate

Ensuite on ajoutera un compte de connexion pour l'administrateur du site 

    sudo php sources/create_admin.php <mot_de_passe>

Vous remplacerez <mot_de_passe> par le vôtre,
il doit contenir au minimum 8 caractères,
1 majuscule, 1 minuscule et 1 chiffre

/!\ ATTENTION : Prenez bien note du mot de passe si vous vous trompez
il faudra effacez manuelement l'utilisateur dans la base de données avant de recommencer /!\

Vous pourrez donc vous connecter en tant qu'administrateur sur le site avec pour information:  
Email: admin@admin  
Mot de passe: Celui que vous avez choisie








