# 2024/2025-SAE_Developpement_Urbain

## Description

Ce projet est une application web destinée à l'analyse et à la visualisation de données géographiques pour le développement urbain. L'application inclut des fonctionnalités comme la comparaison de données, la simulation d'aménagements urbains, et l'affichage de cartes interactives.

## Fonctionnalités

- **Affichage de cartes interactives** : Intégration de fichiers GeoJSON pour afficher des informations géographiques.
- **Comparaison de données** : Comparaison visuelle et analytique de différentes sources de données.
- **Simulation d’aménagements urbains** : Interface pour tester et évaluer divers scénarios d’aménagement.
- **Gestion des utilisateurs** : Authentification, inscription, et gestion des sessions.
- **Visualisation graphique** : Génération de graphiques basés sur les données urbaines.

---

## Structure du projet

### Racine

- `index.php` : Point d'entrée principal de l'application.
- `composer.json` et `composer.lock` : Fichiers de configuration pour la gestion des dépendances PHP avec Composer.
- `phpunit.xml` : Configuration pour les tests unitaires avec PHPUnit.
- `qodana.yaml` : Configuration pour l'analyse de code statique.

### `_assets`

- **`config/`** : Configuration de la base de données et autres paramètres.
- **`fonts/`** : Polices utilisées dans l'application.
- **`images/icons/`** : Icônes et autres ressources graphiques.
- **`scripts/`** : Fichiers JavaScript pour les interactions (ex. : `affichageCarte.js`, `graphiques.js`).
- **`styles/`** : Fichiers CSS pour le style (ex. : `comparaison.css`, `style.css`).
- **`utils/`** : Données et classes utilitaires, notamment des fichiers GeoJSON et des images satellites.
- **`webfonts/`** : Polices web.

### `modules/blog`

- **`controllers/`** : Logique métier de l'application (ex. : `AffichageController.php`, `SimulationController.php`).
- **`models/`** : Modèles de données et gestion des opérations liées à la base de données.
- **`views/`** : Fichiers pour la présentation (ex. : `HomepageView.php`, `GlobalLayout.php`).

### `tests`

Contient des tests unitaires pour valider le bon fonctionnement des modèles.

### `vendor`

Répertoire généré par Composer, contenant les bibliothèques et dépendances tierces, comme :

- **`amenadiel/jpgraph`** : Librairie pour la génération de graphiques.
- **`phayes/geophp`** : Librairie pour la manipulation de données géographiques.

---

## Prérequis

- **PHP >= 7.4**
- **Composer** : Pour gérer les dépendances.
- **Serveur Apache ou Nginx** configuré pour PHP.
- **Base de données MySQL** pour stocker les informations utilisateurs et autres données.

---

## Installation

1. **Cloner le dépôt** :
   ```bash
   git clone <url-du-dépôt>
   cd GARCIA-Leo-2326048b-SAE_Developpement_Urbain
