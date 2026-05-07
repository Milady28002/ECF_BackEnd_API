# Vite & Gourmand - Backend API

## Description

Ce projet correspond au backend de l’application **Vite & Gourmand**, développé avec Symfony dans le cadre de l’Évaluation de Compétences Finale (ECF) du titre professionnel Développeur Web et Web Mobile.

Il expose une **API REST sécurisée** permettant de gérer :

- les utilisateurs  
- les menus et plats  
- les commandes  
- les avis clients  
- les horaires  
- les statistiques administrateur  

Le backend est responsable de la logique métier, de la gestion des données et de la sécurité de l’application.
Il communique avec 
- une base de données relationnelle (MariaDB)
- une base NoSQL (MongoDB) pour les statistiques.

---

## Stack technique

- PHP 8.2+  
- Symfony 7  
- Doctrine ORM  
- MariaDB  
- MongoDB  
- API REST  
- Symfony Mailer 
- Docker (environnement local) 

---

## Fonctionnalités principales

### Authentification
- Inscription utilisateur  
- Connexion sécurisée  
- Gestion des rôles (USER, EMPLOYE, ADMIN)  
- Réinitialisation de mot de passe par email  


### Menus et plats
- CRUD complet
- Gestion des allergènes, régimes et thèmes  
- Gestion du stock  


### Commandes
- Création de commande  
- Calcul dynamique du prix  
- Gestion des statuts :
  - en attente  
  - acceptée  
  - en préparation  
  - en livraison  
  - livrée  
  - terminée  
- Historique des statuts  
- Annulation (client et employé)


### Avis
- Ajout après commande terminée  
- Validation par employé avant publication  


### Emails
- Email de bienvenue  
- Confirmation de commande  
- Réinitialisation de mot de passe  


### Statistiques (MongoDB)
- Nombre de commandes par menu  
- Chiffre d’affaires par menu  
- Filtres par période 

---

## Docker
Le projet utilise Docker pour fournir un environnement de développement complet.

L’environnement comprend :

API Symfony
MariaDB
MongoDB
Mailhog

Les dépendances PHP sont installées automatiquement lors du build Docker.
Le dossier **vendor** est isolé dans un volume Docker afin de ne pas être écrasé par le montage du code local.

---

## Lancer le projet en local (Docker)
Prérequis
- Docker Desktop
- Git

## Installation

1. Cloner les repositories :

```bash
git clone https://github.com/Milady28002/ECF_BackEnd_API.git
git clone https://github.com/Milady28002/ECF_Docker.git
```

2. Lancer l’environnement Docker :
```bash
cd ECF_Docker
docker compose up -d --build
```

3. Initialiser la base de données :
```bash
docker compose exec backend php bin/console doctrine:migrations:migrate
```
---

### Accès
- API -> http://localhost:8000
- Swagger -> http://localhost:8000/api/doc
- Mailhog -> http://localhost:8026

---

## Installation sand Docker

1. Cloner le dépôt :
```bash
git clone https://github.com/Milady28002/ECF_BackEnd_API.git
cd ECF_BackEnd_API
```

2. Installer les dépendances :
```bash
composer install
```

3. Configurer .env.local :
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/db_vite_gourmand"
MAILER_DSN="smtp://localhost:1025"
MONGODB_URL="mongodb://127.0.0.1:27017"
```

4. Créer la base et lancer les migrations :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Lancer le serveur :
```bash
symfony serve
```

---


## Documentation API

Disponible via Swagger (NelmioApiDocBundle) :
```
https://ecfbackendapi-production.up.railway.app/api/doc
```

---

## Déploiement

Backend déployé sur Railway :
```
https://ecfbackendapi-production.up.railway.app/
```
---

## Frontend
```
https://github.com/Milady28002/ECF_FrontEnd
```
---

## Sécurité
- Authentification par token
- Hashage des mots de passe
- Contrôle d’accès basé sur les rôles
- Protection des routes sensibles

Important :
- Un administrateur peut créer des comptes employés
- La création de comptes administrateurs est désactivée côté application

---

## Comptes de test :
```
👤 Utilisateur
Email : dana.scully@user.com
Mot de passe : Azerty@123

🛠️ Employé
Email : employe@vitegourmand.fr
Mot de passe : Admin123!

🔐 Administrateur
Email : admin@vitegourmand.fr
Mot de passe : Admin123!
```
---

## Architecture
- Architecture REST
- Séparation des responsabilités (Controller / Service / Repository)
- Doctrine ORM pour la base relationnelle
- MongoDB pour les données analytiques

---

## 👩‍💻 Autrice
Projet réalisé par Sylvie Mendez (Milady)
Formation Graduate Développeur Web Full Stack
