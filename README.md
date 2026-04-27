# 🍽️ Vite & Gourmand - Backend API

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

---

## Stack technique

- PHP 8.2+  
- Symfony 7  
- Doctrine ORM  
- MariaDB  
- MongoDB  
- API REST  
- Symfony Mailer  

---

## Fonctionnalités principales

### Authentification
- Inscription utilisateur  
- Connexion sécurisée  
- Gestion des rôles (USER, EMPLOYE, ADMIN)  
- Réinitialisation de mot de passe par email  


### Menus et plats
- Création / modification / suppression  
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
- Ajout d’avis après commande terminée  
- Validation par employé avant publication  


### Emails
- Email de bienvenue  
- Confirmation de commande  
- Réinitialisation de mot de passe  


### Statistiques (MongoDB)
- Nombre de commandes par menu  
- Chiffre d’affaires par menu  
- Filtres par période et menu  

---

## Prérequis

- PHP 8.2+  
- Composer  
- Symfony CLI  
- MariaDB ou MySQL  
- MongoDB  
- Serveur mail configuré  

---

## Installation

```bash
1. Cloner le dépôt :
git clone https://github.com/Milady28002/ECF_BackEnd_API.git
cd ECF_BackEnd_API
composer install
```
---

## Configuration
Variable importante

Dans .env.local :

DATABASE_URL="mysql://root:@127.0.0.1:3306/db_vite_gourmand?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
MAILER_DSN="smtp://localhost:1025"
MONGODB_URL="mongodb://127.0.0.1:27017"

---

## Base de données relationnelle
Créer la base :

php bin/console doctrine:database:create

Lancer les migrations :

php bin/console doctrine:migrations:migrate

---

## Base de données NoSQL
MongoDB est utilisée pour stocker les statistiques :

nombre de commandes
chiffre d’affaires

---

## Lancement du serveur
symfony server:start

---

## Documentation API

Disponible via NelmioApiDocBundle (Swagger) :
https://ecfbackendapi-production.up.railway.app/api/doc


---

## Déploiement

🌍Backend déployé sur Railway :
https://ecfbackendapi-production.up.railway.app/

---

Frontend
https://github.com/Milady28002/ECF_FrontEnd

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

👤 Utilisateur
Email : dana.scully@user.com
Mot de passe : Azerty@123

🛠️ Employé
Email : employe@vitegourmand.fr
Mot de passe : Admin123!

🔐 Administrateur
Email : admin@vitegourmand.fr
Mot de passe : Admin123!

---

## Architecture
- Architecture REST
- Séparation des responsabilités (Controller / Service / Repository)
- Doctrine ORM pour la base relationnelle
- MongoDB pour les données analytiques

---

## 👩‍💻 Autrice
Projet réalisé par Sylvie Mendez alias Milady
Formation Graduate Développeur Web Full Stack
