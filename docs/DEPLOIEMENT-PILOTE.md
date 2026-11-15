# SGPE — Guide de déploiement pilote

Guide d'exploitation pour déployer le Système de Gestion du Patrimoine de
l'État (Togo) en environnement pilote.

## 1. Architecture

| Composant | Rôle | Port interne |
|---|---|---|
| `service-auth` | API d'authentification (Laravel Sanctum) | 8000 (→ 127.0.0.1:8001) |
| `service-patrimoine` | Interface web + API métier | 80 (→ 127.0.0.1:8002) |
| `postgres` | Base PostgreSQL 15 partagée | 5432 (→ 127.0.0.1:5432) |

Les trois services partagent la même base `patrimoine_etat`.

## 2. Prérequis serveur

- Docker + Docker Compose v2
- Un reverse proxy HTTPS (nginx, Caddy ou équivalent) sur le même hôte
- Un nom de domaine (ex. `sgpe.exemple.tg`) avec certificat TLS
- Espace disque pour les sauvegardes PostgreSQL

## 3. Installation

```bash
git clone <url-du-depot> gestion-patrimoine-etat
cd gestion-patrimoine-etat/code
```

### 3.1 Variables d'environnement

Créer `code/.env` (lu par docker compose, y compris avec le fichier
`docker-compose.prod.yml`) :

```env
# Base de données
DB_DATABASE=patrimoine_etat
DB_USERNAME=sgpe
DB_PASSWORD=<mot-de-passe-fort>

# Application web (service-patrimoine)
APP_URL=https://sgpe.exemple.tg
PATRIMOINE_APP_KEY=base64:<php artisan key:generate --show>

# API d'authentification (service-auth)
AUTH_URL=http://127.0.0.1:8001
AUTH_APP_KEY=base64:<php artisan key:generate --show>
```

Générer les clés avec `php artisan key:generate --show` dans chaque
service (ou `openssl rand -base64 32` préfixé de `base64:`). Les fichiers
`.env` sont ignorés par git — ne jamais les committer.

### 3.2 Lancement

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

### 3.3 Migrations et administrateur initial

```bash
docker compose exec service-patrimoine php artisan migrate --force
docker compose exec service-patrimoine php artisan sgpe:admin:create
```

La commande `sgpe:admin:create` est interactive (nom, email, mot de passe
masqué, confirmation) et crée le premier compte `ADMIN`.

## 4. Reverse proxy (exemple nginx)

```nginx
server {
    listen 443 ssl;
    server_name sgpe.exemple.tg;

    ssl_certificate     /etc/letsencrypt/live/sgpe.exemple.tg/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sgpe.exemple.tg/privkey.pem;

    # Interface web + API métier
    location / {
        proxy_pass http://127.0.0.1:8002;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # API d'authentification (si appelée directement)
    location /auth-api/ {
        proxy_pass http://127.0.0.1:8001/;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 5. Sauvegarde et restauration

### Sauvegarde quotidienne (cron)

```bash
docker compose exec -T postgres \
  pg_dump -U sgpe -d patrimoine_etat -Fc \
  > /var/backups/sgpe/$(date +%F).dump
```

Conserver au moins 30 jours de sauvegardes, idéalement sur un support
distinct ou hors site.

### Restauration

```bash
docker compose exec -T postgres \
  pg_restore -U sgpe -d patrimoine_etat --clean --if-exists \
  < /var/backups/sgpe/AAAA-MM-JJ.dump
```

## 6. Exploitation courante

| Action | Commande |
|---|---|
| Voir les logs | `docker compose logs -f service-patrimoine` |
| Redémarrer | `docker compose restart` |
| Mettre à jour | `git pull && docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build && docker compose exec service-patrimoine php artisan migrate --force` |
| Reset mot de passe admin | `docker compose exec service-patrimoine php artisan sgpe:admin:password-reset` |
| Vider le cache | `docker compose exec service-patrimoine php artisan optimize:clear` |

## 7. Points de vigilance pilote

- Activer HTTPS partout (`SESSION_SECURE_COOKIE=true`).
- Restreindre l'accès réseau : seuls 80/443 publics, PostgreSQL sur
  localhost uniquement.
- Prévoir le plan de sauvegarde **avant** la mise en production.
- Rôles à attribuer : `ADMIN` (administrateurs SGPE), `GESTIONNAIRE`
  (agents qui saisissent), `CHEF_SERVICE` (validation des mouvements).
- Les règles de réforme/déclassement des biens et le périmètre
  administratif pilote restent à formaliser avec le commanditaire.
