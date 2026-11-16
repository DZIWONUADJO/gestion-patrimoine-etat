# service-patrimoine — Application métier SGPE

Service Laravel 12 portant toute la logique métier du SGPE (République Togolaise) : inventaire des biens de l'État, affectations, maintenance, contrôles et rapports.

## Modules

- **Biens** — CRUD complet, catégories, services, numéros d'inventaire, états, justificatifs, archivage
- **Affectations** — proposition, validation, rejet, annulation ; historique des mouvements
- **États & maintenance** — historique des changements d'état, cycle d'interventions (réparation, maintenance, visite technique)
- **Inventaires** — campagnes de contrôle avec relevés par bien et clôture figée
- **Rapports** — exports PDF et CSV de l'inventaire et des mouvements
- **Tableau de bord** — indicateurs d'activité et de parc
- **Interface web** — Blade + Tailwind, sessions ; **API** — Sanctum, mêmes règles métier

## Rôles

| Rôle | Droits |
|---|---|
| `ADMIN` | Administration complète, référentiels, archivage, validation, rapports |
| `GESTIONNAIRE` | Gestion opérationnelle des biens, propositions d'affectation |
| `CHEF_SERVICE` | Consultation et validation des affectations en attente |

## Routes web principales

`/connexion` · `/` (tableau de bord) · `/biens` · `/mouvements` · `/inventaires` · `/rapports`

## Configuration

Variables d'environnement principales (voir `.env.example`) :

- `APP_URL` — URL publique (ex. `http://localhost:8002`)
- `DB_*` — connexion PostgreSQL (base partagée avec `service-auth`)

## Tests

```bash
./vendor/bin/phpunit
```

## Identité visuelle

Interface alignée sur la charte graphique de la République Togolaise :

- Vert institutionnel `#006A4F` · Or `#FFCF11` · Rouge `#D11135`
- Armoiries officielles (`public/images/armoiries-togo.svg`)

---

*SGPE — Système de Gestion du Patrimoine de l'État, République Togolaise.*
