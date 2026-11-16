# service-auth — API d'authentification SGPE

Service Laravel 12 exposant l'authentification de la plateforme SGPE (République Togolaise).

## Rôle

- Authentification des utilisateurs (e-mail / mot de passe)
- Émission et validation de jetons **Laravel Sanctum** pour l'API
- Gestion des comptes et de leurs rôles (`ADMIN`, `GESTIONNAIRE`, `CHEF_SERVICE`)
- Blocage des comptes désactivés

## Endpoints principaux

| Méthode | Route | Description |
|---|---|---|
| POST | `/api/login` | Connexion, retourne un jeton d'accès |
| POST | `/api/logout` | Révocation du jeton courant |
| GET | `/api/me` | Profil de l'utilisateur authentifié |

## Configuration

Variables d'environnement principales (voir `.env.example`) :

- `APP_URL` — URL publique du service (ex. `http://localhost:8001`)
- `DB_*` — connexion PostgreSQL
- `SANCTUM_STATEFUL_DOMAINS` — domaines autorisés pour les cookies de session

## Tests

```bash
./vendor/bin/phpunit
```

---

*SGPE — Système de Gestion du Patrimoine de l'État, République Togolaise.*
