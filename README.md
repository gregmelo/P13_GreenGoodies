# Green Goodies – Application e‑commerce Symfony

Green Goodies est une boutique en ligne spécialisée dans les produits écologiques.
Ce projet Symfony permet de parcourir un catalogue, gérer un panier, créer un compte client, passer commande et consommer une API sécurisée pour récupérer la liste des produits.

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation du projet](#installation-du-projet)
- [Configuration](#configuration)
  - [Base de données](#base-de-données)
  - [Variables d’environnement](#variables-denvironnement)
  - [Accès API et JWT](#accès-api-et-jwt)
- [Démarrage de l’application](#démarrage-de-lapplication)
- [Données de test (fixtures)](#données-de-test-fixtures)
- [Fonctionnalités détaillées](#fonctionnalités-détaillées)
  - [Navigation et page d’accueil](#navigation-et-page-daccueil)
  - [Catalogue produits](#catalogue-produits)
  - [Panier](#panier)
  - [Compte utilisateur](#compte-utilisateur)
  - [Authentification](#authentification)
- [Documentation de l’API](#documentation-de-lapi)
  - [Authentification API – POST /api/login](#authentification-api--post-apilogin)
  - [Liste des produits – GET /api/products](#liste-des-produits--get-apiproducts)
- [Tests](#tests)
- [Qualité / bonnes pratiques](#qualité--bonnes-pratiques)

---

## Fonctionnalités

- Parcours du **catalogue de produits** (liste + page détail).
- Gestion d’un **panier** (ajout, suppression, validation).
- Création de **compte client** et **connexion**.
- Espace **Mon compte** pour consulter ses informations.
- Gestion des **commandes** (entités `Order` et `OrderItem`).
- **API JSON** sécurisée par token JWT :
  - `POST /api/login` pour obtenir un token.
  - `GET /api/products` pour récupérer la liste des produits.
- Activation / désactivation de l’**accès API** par utilisateur (champ booléen `apiAccess`).

---

## Technologies utilisées

- **Langage** : PHP ≥ 8.2
- **Framework** : Symfony 7.4
- **Base de données** : MySQL (gérée via phpMyAdmin) avec Doctrine ORM
- **Template** : Twig
- **Front** :
  - Symfony Asset Mapper
  - Stimulus (symfony/stimulus-bundle)
  - CSS classique (fichiers dans `assets/styles`)
- **Sécurité** :
  - Composant Security de Symfony (authentification par formulaire)
  - Hashage de mots de passe via `PasswordAuthenticatedUserInterface`
  - JWT avec la librairie `firebase/php-jwt`
- **Tests** : PHPUnit

---

## Installation du projet

1. **Cloner le dépôt**

```bash
git clone <url-du-dépôt>
cd P13_GreenGoodies
```

2. **Installer les dépendances PHP**

```bash
composer install
```

---

## Configuration

### Base de données

1. Configurer la variable de connexion dans le fichier d’environnement (par exemple `.env` ou `.env.local`) :

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/green_goodies?serverVersion=8.0"
```

La base est gérée en local via **phpMyAdmin**. Adapter `user`, `password`, l’hôte, le port et le nom de la base selon votre environnement (WAMP/XAMPP, etc.).

2. Créer la base de données :

```bash
php bin/console doctrine:database:create
```

3. Lancer les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

### Variables d’environnement

Les variables importantes :

```env
APP_ENV=dev
APP_SECRET=...
JWT_SECRET=une-chaîne-longue-et-secrète
```

- `JWT_SECRET` est utilisée pour signer et valider les tokens JWT de l’API.

### Accès API et JWT

- L’entité `User` contient un booléen `apiAccess` initialisé à `false`.
- Seuls les utilisateurs ayant `apiAccess = true` peuvent :
  - obtenir un token via l’API (`/api/login`),
  - consommer l’API produits (`/api/products`).

Le service `JwtService` est responsable de :

- générer un token JWT à partir d’un utilisateur connecté (`generateToken()`),
- décoder et valider un token reçu (`decodeToken()`).

Il utilise la librairie `firebase/php-jwt` et une durée de validité configurable (TTL).

---

## Démarrage de l’application

1. **Lancer le serveur Symfony**

Avec le binaire Symfony :

```bash
symfony serve
```

ou via le serveur PHP interne :

```bash
php -S 127.0.0.1:8000 -t public
```

2. Ouvrir le navigateur sur l’URL affichée (généralement `http://127.0.0.1:8000`).

---

## Données de test (fixtures)

Le projet contient des fixtures (données de démonstration) pour peupler la base.

Pour les charger :

```bash
php bin/console doctrine:fixtures:load
```

Cette commande :

- crée des utilisateurs de test,
- insère des produits de démonstration.

---

## Fonctionnalités détaillées

### Navigation et page d’accueil

- La page d’accueil affiche une sélection de produits.
- Le layout commun (header, footer) contient :
  - un lien vers le catalogue,
  - un accès au panier,
  - un accès à l’espace compte / login.

### Catalogue produits

- Liste des produits avec :
  - nom,
  - description courte,
  - prix,
  - visuel.
- Page détail du produit :
  - description complète,
  - prix,
  - bouton pour ajouter au panier.

### Panier

- Ajout d’un produit depuis la fiche produit.
- Suppression d’un produit du panier.
- Calcul du total du panier.

### Compte utilisateur

- Inscription via un formulaire avec validation.
- Connexion via email + mot de passe.
- Page compte affichant la liste des commandes, un bouton pour activer l'API et un bouton de suppression du compte.

### Authentification

- Authentification par formulaire classique côté site (login HTML).
- Gestion des rôles (au minimum `ROLE_USER` pour les utilisateurs connectés).
- Protection de certaines routes (compte, panier) aux utilisateurs connectés via la configuration de sécurité.

---

## Documentation de l’API

L’API respecte les spécifications techniques fournies par le client Green Goodies.

### Authentification API – POST `/api/login`

- **URL** : `/api/login`
- **Méthode** : `POST`
- **Body (JSON)** :

```json
{
  "username": "email@exemple.com",
  "password": "motdepasse"
}
```

> Le champ `username` correspond à l’email de l’utilisateur.

- **Réponses possibles** :

1. **Identifiants corrects & accès API activé**

   - **Statut** : `200 OK`
   - **Body** :

   ```json
   {
     "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6..."
   }
   ```

2. **Identifiants incorrects (email ou mot de passe)**

   - **Statut** : `401 Unauthorized`
   - **Body** (exemple) :

   ```json
   {
     "error": "Identifiants incorrects"
   }
   ```

3. **Accès API non activé pour l’utilisateur**

   - **Statut** : `403 Forbidden`
   - **Body** (exemple) :

   ```json
   {
     "error": "Accès API non activé"
   }
   ```

4. **Requête invalide (JSON manquant ou champs absents)**

   - **Statut** : `400 Bad Request`
   - **Body** (exemple) :

   ```json
   {
     "error": "Requête invalide"
   }
   ```

### Liste des produits – GET `/api/products`

- **URL** : `/api/products`
- **Méthode** : `GET`
- **Authentification requise** : Oui (token JWT dans le header)

- **Header d’authentification** :

```http
Authorization: Bearer <token>
```

- **Réponse 200 – Succès**

  - **Statut** : `200 OK`
  - **Body** : tableau de produits

  ```json
  [
    {
      "id": 1,
      "name": "Nom du produit",
      "shortDescription": "Description courte",
      "fullDescription": "Description complète...",
      "price": 19.99,
      "picture": "url_ou_nom_de_fichier.jpg"
    },
    {
      "id": 2,
      "name": "Autre produit",
      "shortDescription": "Description courte",
      "fullDescription": "Description complète...",
      "price": 9.99,
      "picture": "autre.jpg"
    }
  ]
  ```

- **Réponses d’erreur possibles** :

1. **Token manquant**

   - **Statut** : `401 Unauthorized`
   - **Body** (exemple) :

   ```json
   {
     "error": "Token manquant"
   }
   ```

2. **Token invalide ou expiré**

   - **Statut** : `401 Unauthorized`
   - **Body** (exemple) :

   ```json
   {
     "error": "Token invalide ou expiré"
   }
   ```

3. **Utilisateur introuvable (supprimé depuis l’émission du token)**

   - **Statut** : `401 Unauthorized`

4. **Accès API non activé**

   - **Statut** : `403 Forbidden`
   - **Body** (exemple) :

   ```json
   {
     "error": "Accès API non activé"
   }
   ```

Toutes les erreurs sur les routes commençant par `/api` sont renvoyées au format JSON grâce à un subscriber d’exception dédié.

---

## Tests

L’infrastructure de tests (PHPUnit, configuration) est installée, mais **aucun test automatisé n’a été implémenté pour ce projet**.

Il est toutefois possible d’ajouter des tests unitaires et/ou fonctionnels, par exemple pour :

- la logique du panier,
- l’authentification,
- l’API (`/api/login` et `/api/products`).

---

## Qualité / bonnes pratiques

- Respect des bonnes pratiques Symfony (services autowirés, séparation claire des couches : contrôleurs, services, entités, formulaires, etc.).
- Validation des données en entrée via le composant Validator.
- Séparation front/back :
  - templates Twig propres et réutilisables,
  - assets front organisés (JavaScript, CSS).
- API sécurisée :
  - JWT pour l’authentification,
  - restriction d’accès via le champ `apiAccess`,
  - gestion unifiée des erreurs API en JSON.

---

## Décisions techniques

- **JWT sans bundle lourd** : l’authentification API repose sur la librairie `firebase/php-jwt` encapsulée dans un service `JwtService`, ce qui évite de dépendre d’un bundle tiers comme LexikJWTBundle et reste suffisant pour les besoins du projet.
- **Contrôle d’accès API par champ `apiAccess`** : plutôt que d’introduire des rôles spécifiques, un booléen `apiAccess` sur l’entité `User` permet d’activer/désactiver clairement l’accès à l’API, conformément à la demande du client.
- **Vérification du token côté contrôleur** : le token JWT est vérifié directement dans les contrôleurs API (`ApiProductController`), ce qui rend la logique très explicite pour un projet pédagogique.
- **Gestion unifiée des erreurs JSON** : un `ApiExceptionSubscriber` intercepte les exceptions sur les routes commençant par `/api` pour renvoyer systématiquement une réponse JSON (y compris pour les erreurs 404/405 générées par Symfony).
- **Séparation site / API** : les routes "site" restent basées sur les sessions et formulaires de Symfony, tandis que l’API est strictement stateless et utilise des tokens dans le header `Authorization`.

---

## Auteur

- Nom : Véricel Grégory
- Projet : P13 – Green Goodies (OpenClassrooms / Symfony)
