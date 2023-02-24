# GDDWWMECFENTRIII2A

ÉVALUATION D’ENTRAÎNEMENT EN COURS DE FORMATION :  
Développer la partie back-end d’une application web

Lien github du projet: https://github.com/Pinguin211/GDDWWMECFENTRIII2A.git

## Pour commencer

Les instructions sont pour un serveur sous linux Ubuntu (LAMP)  
Vous aurez aussi besoin des privilege de super-utilisateurs sur cette machine

### Pré-requis

Afin de pouvoir exécuter l'application sur votre poste, vous devez 
d'abord installer les dépendances suivantes :
* Apache2
* Php8.2
* MySql (Si la base de données est en local)
* NodeJs
* composer
* git

### Installation
Ci-dessous les liens et tutoriels d'installation pour les prérequis

#### Apache2 - https://doc.ubuntu-fr.org/apache2

#### Php (Version 8 minimum) et extensions
    sudo add-apt-repository ppa:ondrej/php
    sudo apt update
    sudo apt install php8.2 php8.2-cli php8.2-{curl,bz2,mbstring,intl,xml,mysql}
    sudo apt install php-mysql
    sudo apt install php-xml
#### MySql - https://doc.ubuntu-fr.org/mysql

#### Nodejs (VERSION 19)
    curl -fsSL https://deb.nodesource.com/setup_19.x | sudo -E bash -
    sudo apt-get update && sudo apt-get install -y nodejs

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

### Creation de la base de données

Faites cette commande pour lancer Mysql

    sudo mysql

Suivez ce tutoriel pour créer un utilisateur et lui donné les droits à mysql  
https://www.hostinger.fr/tutoriels/creer-un-utilisateur-mysql

Démarrer mysql avec cette commande

    mysql -u <nom_de_votre_utilisateur> -p

Insérer votre mot de passe et une fois dans mysql exécuter la commande suivante pour créer une base de donné

    create database <nom_de_votre_base_de_donné>;

Ensuite, exit pour quitter Mysql

    exit;

---
---

_**Si vous souaihter remplir la base de donner avec des faux element de test,
vous pouvez suivre les commande suivante sinon passez directement à l'installation des bibliothèques**_

    mysql -u <nom_de_votre_utilisateur> -p <le_nom_de_votre_base_de_données> < sources/dump.sql

Il faut remplacer <le_nom_de_votre_base_de_données> par le nom de votre base de donnés  
Si vous effectuer cette étape vous pourrez passer l'étape de la migration de la base de donné


---
---
### Installation des bibliothèques du projet

Une fois dans le dossier du projet (vous devriez voir sa) :

[image](sources/img/1.png)

---
Vous devrez ensuite configurer la base de données, verifier que votre base de données soit bien activé,
vous effectuerez la commande suivante pour lancer le script

    sudo php sources/set_data_base.php

Vous devez remplir les informations concernant votre base de données  
(ci dessous les informations sont pour une base de données en local)

[image](sources/img/2.png)

Ce script aura pour effet d'inscrire dans le fichier .env.local les données de connexions à la base de donné et
les paramètre de l'environnement, il devrait ressembler à ça :

[image](sources/img/3.png)

---

Il faudra ensuite effectuer cette commande pour télécharger toutes les dépendances et
l'outil qui permettra de verifier si les dépendances sont completes

    sudo composer require symfony/requirements-checker

---

Ensuite il faut execute la commande suivante pour mettre à jour les dépendances
et retiré c equi ne servent pas

    sudo composer install --no-dev --optimize-autoloader

Vous devriez avoir ce résultat :

[image](sources/img/4.png)

---

Vous allez récupérer les dépendances à node.js avec la command suivante

    sudo npm install

Il faut maintenant compiler les assets avec la commande suivante

    sudo npm run build

Vous devriez avoir ce résultat :

[image](sources/img/5.png)

---

### Configuration du projet

Vous effectuerez les commandes suivantes pour créer les dossiers de logs, cache et des CV

    sudo mkdir var/log
    sudo mkdir var/cache
    sudo mkdir public/cv

(Si le dossier est deja créer ce n'est pas obligatoire)

Puis faites les commandes suivantes pour permettre à l'application d'écrire dans les dossiers

    sudo chmod -R 777 var/log
    sudo chmod -R 777 var/cache
    sudo chmod 777 public/cv

---

**Vous pouvez sauter cette étape si vous avez deja remplie la base de données au début**  
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
il faudra effacez manuellement l'utilisateur dans la base de données avant de recommencer /!\

Vous pourrez donc vous connecter en tant qu'administrateur sur le site avec pour information:  
Email : admin@admin  
Mot de passe : Celui que vous avez choisi

---

Vous allez ensuite vider les cache avec cette commande

    sudo APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

---

Pour finir vous effectuerez les commandes suivantes pour désactiver la page par défauts d'apache2,
déplacer le fichier de configuration apache2 et recharger apache

    sudo a2dissites 000-default.conf
    sudo sources/copie_conf.sh
    sudo systemctl reload apache2.service

---

Vous pouvez maintenant ouvrir votre serveur au réseau est vous connectez

[image](sources/img/6.png)




