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

### Installation
Ci-dessous les liens et tutoriels d'installation pour les prérequis

#### Apache2 - https://doc.ubuntu-fr.org/apache2

#### Php (Version 8 minimum) - https://doc.ubuntu-fr.org/php

#### MySql - https://doc.ubuntu-fr.org/mysql

#### Nodejs - https://doc.ubuntu-fr.org/nodejs

#### Composer - https://doc.ubuntu-fr.org/composer

#### Git - https://doc.ubuntu-fr.org/git

---  
---

## Démarrage

Pour chaque étape il y aura les commandes associées

### Clonage du projet

Sur votre machine dirigée vous vers le dossier `/var/www`

    cd /var/www

---

Puis cloner le projet à partir de son url

    sudo git clone https://github.com/Pinguin211/GDDWWMECFENTRIII2A.git

---

Placer vous ensuite dans le dossier du projet

    cd GDDWWMECFENTRIII2A

---

### Installation des bibliothèques du projet

Une fois dans le dossier du projet (vous devriez voir sa) :

# PHOTO

---
Vous devrez ensuite configurer la base de données, verifier que votre base de données soit bien activé,
vous effectuerez la commande suivante pour lancer le script

    sudo php sources/set_data_base.php

Vous devez remplir les informations concernant votre base de données  
(ci dessous les informations sont pour une base de données en local)

# PHOTO

Ce script aura pour effet d'inscrire dans le fichier .env.local les données de connexions à la base de donné et
les paramètre de l'environnement, il devrait ressembler à ça :

# PHOTO

---

Il faudra ensuite effectuer cette commande pour télécharger toutes les dépendances et
l'outil qui permettra de verifier si les dépendances sont completes

    composer require symfony/requirements-checker

---

Ensuite il faut execute la commande suivante pour mettre à jour les dépendances
et retiré c equi ne servent pas

    sudo composer install --no-dev --optimize-autoloader

Vous devriez avoir se resultat :

# PHOTO

---

### Configuration du projet

Vous effectuerez la commande suivante pour créer le dossier de logs
(Si le dossier est deja créer ce n'est pas obligatoire)

    sudo mkdir var/log && chmod 777 var/log

---

Si la base de données n'est pas créé vous pouvez faire la commande suivante,
sinon passer directement à l'étape suivante

    sudo php bin/console doctrine:database:create

---

Ensuite vous effectuerez la migration pour créer les tables nécessaires à la base de données  
**Votre base de données doit être vide pour effectuer ce script sinon cela pourrait supprimer vos données**

    sudo php bin/console doctrine:migration:migrate

---

Ensuite on ajoutera un compte de connexion pour l'administrateur du site 

    sudo php sources/create_admin.php <mot_de_passe>

Vous remplacerez <mot_de_passe> par le vôtre,
il doit contenir au minimum 8 caractères,
1 majuscule, 1 minuscule et 1 chiffre

/!\ ATTENTION : Prenez bien note du mot de passe si vous vous trompez
il faudra effacez manuelement l'utilisateur dans la base de données avant de recommencer /!\

Vous pourrez donc vous connecter en tant qu'administrateur sur le site avec pour information:  
Email: admin@admin  
Mot de passe: Celui que vous avez choisi

---

Il faut maintenant compiler les assets avec la commande suivante

    sudo npm run build

---

Vous allez ensuite vider les cache avec cette commande

    sudo APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear









