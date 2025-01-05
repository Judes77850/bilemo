Bilemo

Bilemo est une API RESTful permettant aux entreprises clientes de gérer une liste de produits (tels que des téléphones mobiles) et leurs utilisateurs associés. Cette API est conçue avec Symfony et suit les meilleures pratiques en matière de développement.
Fonctionnalités

Gestion des produits :
Ajouter, modifier, et supprimer des produits.
Lister les produits disponibles.
Gestion des utilisateurs :
Créer des utilisateurs associés à un client.
Lister les utilisateurs associés.
Système d'authentification sécurisé via JWT.
Documentation interactive de l'API avec Swagger/OpenAPI.
Structure respectant les principes de séparation des responsabilités grâce à Symfony Messenger.
Prérequis

PHP >= 8.1
Composer >= 2.0
Symfony CLI (facultatif, mais recommandé)
Base de données (ex. MySQL, PostgreSQL ou SQLite)

Installation

Clonez le dépôt :
git clone https://github.com/Judes77850/bilemo.git
cd bilemo

Installez les dépendances backend :
composer install

Configurez l'environnement en créant un fichier .env.local :
cp .env .env.local

Ajustez les variables d'environnement comme DATABASE_URL selon votre configuration.

Mettez en place la base de données :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Démarrez le serveur de développement :
symfony server:start
L'API sera accessible par défaut sur http://localhost:8000.

Documentation de l'API

Une documentation interactive est disponible grâce à Swagger/OpenAPI. Accédez-y en ouvrant l'URL suivante dans votre navigateur :
http://127.0.0.1:8000/api/doc#

Authentification

L'API utilise JWT pour l'authentification. Voici comment obtenir un token d'accès :
Faites une requête POST vers /api/login_check avec les informations d'identification de l'utilisateur :
{
    "username": "user@example.com",
    "password": "password"
}
Vous recevrez un token JWT à inclure dans l'en-tête Authorization pour les requêtes suivantes :
Authorization: Bearer <token>
