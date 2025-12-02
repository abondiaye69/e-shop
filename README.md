# e-shop

Boutique Symfony prête à l’emploi avec front inspiré sneakers/bag et panier localStorage.

## Prérequis
- PHP 8.2+, Composer
- Symfony CLI (optionnel mais pratique)
- Node/npm si vous voulez builder les assets front (si présents)

## Installation
```bash
composer install
cp .env .env.local   # puis ajuster vos variables (BD, mailer, etc.)
```

## Base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Lancer le serveur
```bash
symfony serve -d   # ou php -S localhost:8000 -t public
```

## Tests
```bash
php bin/phpunit
```

## Déploiement
- Ne pas committer vos fichiers `.env*` (ils sont ignorés).
- Configurer les variables d’environnement sur l’hébergeur (BD, APP_ENV, APP_SECRET, MAILER_DSN).
