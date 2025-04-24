# Projet Click-Journey : Répartition des Tâches - Phase 1

### Informations Générales

- **Projet :** Click-Journey
- **Filière :** préING2 - 2024-2025
- **Membres du groupe :** Mathias DA COSTA SILVA, Romain DECRAND LARDIERE, Victor HAEGEMAN
- **Date de début :** 15/02/2025

---

## Répartition des Tâches

### Victor HAEGEMAN

- **Fichiers travaillés :** `profile.html`, `style.css`
- **Tâches :**
  - Création de la page de profil utilisateur avec boutons de modification.
  - Intégration de Tailwind CSS et structure de base des styles.

---

### Romain DECRAND LARDIERE

- **Fichiers travaillés :** `admin.html`, `liste.html`
- **Tâches :**
  - Création de la page administrateur affichant la liste des utilisateurs.
  - Ajout des boutons d'administration pour modifier les propriétés des utilisateurs.

---

### Mathias DA COSTA SILVA

- **Fichiers travaillés :** `index.html`, `connexion.html`, `inscription.html`
- **Tâches :**
  - Création de la page d'accueil avec présentation du site.
  - Mise en place des formulaires d'inscription et de connexion.

---

## Organisation et Collaboration

- **Travail :** Localement sur le même PC les 15/02/2025 et 16/02/2025.
- **Gestion de projet :** Établissement de la charte graphique, discussion autour des fonctionnalités, répartition des tâches, puis code ! (heureusement).

---

## Problème Rencontré et Solution

- **Problème :** Difficultés d'installation de Tailwind CSS via npm.
  - **Solution :** Utilisation de la documentation officielle Tailwind pour Vite.

---

_Ce document sera mis à jour au fur et à mesure de l'avancement du projet._

---

# Phase 2

- **Date de début :** 01/03/2025
- **Date de fin estimée :** 31/03/2025

---

## Répartition des Tâches

### Victor HAEGEMAN

- **Fichiers travaillés :** `data/users.json`, `data/voyages.json`, `www/includes/db.php`, `doc/database_model.md`
- **Tâches :**
  - Modélisation des structures de données pour les utilisateurs et les voyages.
  - Mise en place de la logique serveur initiale pour la gestion des données (lecture/écriture fichiers JSON).
  - Documentation du modèle de données.

---

### Romain DECRAND LARDIERE

- **Fichiers travaillés :** `www/admin.php`, `www/admin_users.php`, `www/includes/auth.php`, `www/assets/css/admin.css`
- **Tâches :**
  - Développement de l'interface d'administration pour la gestion des utilisateurs (CRUD).
  - Sécurisation de l'accès à la section admin.
  - Structuration et stylisation de la partie administration.

---

### Mathias DA COSTA SILVA

- **Fichiers travaillés :** `www/index.php`, `www/connexion.php`, `www/inscription.php`, `www/voyages.php`, `www/voyage-details.php`, `www/personnalisation.php`, `www/paiement.php`, `www/includes/header.php`, `www/includes/footer.php`
- **Tâches :**
  - Implémentation du système d'inscription et de connexion utilisateur.
  - Création des pages d'affichage des voyages, détails de voyage, personnalisation et simulation de paiement.
  - Mise en place de la structure globale des pages (header/footer).

---

## Organisation et Collaboration

- **Travail :** Sessions de pair programming régulières, utilisation de Git et GitHub pour la collaboration à distance.
- **Gestion de projet :** Points hebdomadaires pour synchroniser l'avancement, revue de code croisée. Mise à jour du rapport de projet.

---

## Problèmes Rencontrés et Solutions

- **Problème :** Gestion complexe des sessions PHP et de l'état de connexion sur plusieurs pages.
  - **Solution :** Centralisation de la logique d'authentification dans `check_auth.php` et utilisation cohérente des sessions `$_SESSION`.
- **Problème :** Synchronisation des structures de données JSON entre les différentes fonctionnalités.
  - **Solution :** Définition claire du schéma JSON dans `database_model.md` et communication constante entre les membres.
