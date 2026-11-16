<div align="center">

<img src="code/service-patrimoine/public/images/armoiries-togo.svg" alt="Armoiries de la République Togolaise" width="110">

# SGPE — Système de Gestion du Patrimoine de l'État

**République Togolaise**

Application web de centralisation, de suivi et de contrôle du patrimoine de l'État togolais : inventaire des biens, affectations et transferts, états et maintenance, campagnes d'inventaire, rapports et indicateurs.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-4169E1?logo=postgresql&logoColor=white)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docker.com)

</div>

---

## Fonctionnalités

- **Authentification et rôles** — trois profils : `ADMIN`, `GESTIONNAIRE`, `CHEF_SERVICE`, avec contrôle d'accès par route et par action
- **Inventaire des biens** — catégories, services, numéros d'inventaire, états, justificatifs téléversés, archivage et restauration
- **Affectations et transferts** — circuit de proposition / validation / rejet / annulation avec historique complet
- **États et maintenance** — historique horodaté des changements d'état, interventions de réparation avec prestataire et coût
- **Contrôles d'inventaire** — campagnes par service, relevés par bien (`PRÉSENT` / `ANOMALIE` / `INTROUVABLE`), clôture figée des relevés
- **Rapports** — exports PDF et CSV (compatibles Excel) de l'inventaire et des mouvements
- **Tableau de bord** — indicateurs d'activité, répartition du parc par état, anomalies récentes
- **Identité visuelle** — charte graphique de la République Togolaise (vert `#006A4F`, or `#FFCF11`, rouge `#D11135`)

## Architecture

| Composant | Rôle | Port local |
|---|---|---|
| `service-auth` | API d'authentification (Laravel Sanctum) | `8001` |
| `service-patrimoine` | Application métier (interface web + API) | `8002` |
| `postgres` | Base de données PostgreSQL 15 | `5432` |

Les deux services Laravel communiquent via API ; l'interface web utilise des sessions, l'API mobile des jetons Sanctum.

## Démarrage rapide

Prérequis : **Docker** et **Docker Compose**.

```bash
cd code
docker compose up -d --build
```

Puis appliquer les migrations :

```bash
docker exec service_patrimoine php artisan migrate --force
```

Ouvrir l'application : **http://localhost:8002**

### Comptes de démonstration (environnement local)

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | `demo@sgpe.local` | `demo1234` |
| Gestionnaire | `gestionnaire@sgpe.local` | `demo1234` |
| Chef de service | `chef@sgpe.local` | `demo1234` |

> Ces comptes sont réservés au développement et à la démonstration. En production, créez les comptes via l'interface d'administration.

## Structure du dépôt

```
├── code/
│   ├── docker-compose.yml          # Environnement de développement
│   ├── docker-compose.prod.yml     # Surcharge production (HTTPS-ready)
│   ├── service-auth/               # API d'authentification (Laravel 12)
│   └── service-patrimoine/         # Application métier (Laravel 12)
│       ├── app/                    # Contrôleurs, modèles, services métier
│       ├── database/migrations/    # Schéma PostgreSQL
│       ├── resources/views/        # Interfaces Blade + Tailwind
│       └── public/images/          # Armoiries et ressources graphiques
├── docs/
│   ├── DEPLOIEMENT-PILOTE.md       # Guide de déploiement et exploitation
│   ├── Analyse du besoin *.docx    # Cadrage fonctionnel
│   ├── UML/                        # Diagrammes
│   └── maquettes/                  # Maquettes de référence
└── README.md
```

## Tests

```bash
cd code/service-patrimoine && ./vendor/bin/phpunit
cd code/service-auth && ./vendor/bin/phpunit
```

La suite couvre les rôles, les circuits d'affectation, les états historisés, la maintenance, les inventaires et les exports.

## Déploiement

Le guide complet (serveur, variables d'environnement, reverse proxy nginx, HTTPS, sauvegardes PostgreSQL) est disponible dans [`docs/DEPLOIEMENT-PILOTE.md`](docs/DEPLOIEMENT-PILOTE.md).

En résumé :

```bash
cd code
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

## Charte graphique

L'interface respecte la charte graphique de la République Togolaise :

- **Vert institutionnel** `#006A4F` — actions principales, navigation
- **Or** `#FFCF11` — accents et éléments actifs
- **Rouge** `#D11135` — erreurs et alertes
- Armoiries officielles dans l'authentification, la navigation et les pages d'erreur

---

*Système de Gestion du Patrimoine de l'État — République Togolaise.*
