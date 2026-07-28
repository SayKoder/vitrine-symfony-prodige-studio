# VitrinePS

Site vitrine et gestion de devis/commande pour une activite de photographe en
entreprise individuelle (EI) : catalogue de prestations photo/video, panier,
promotions, en PWA responsive. Symfony 7, construit progressivement par etapes
numerotees.

## Lancement en local

Prerequis : Docker et Docker Compose.

```
cp .env.example .env.local
docker compose build
docker compose up -d
docker compose exec php php bin/console doctrine:database:create
```

Le site est ensuite accessible sur http://localhost:8080.

Des comptes et des prestations de demonstration sont crees par les fixtures :

```
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

- 3 prestations de demonstration, gerables depuis /admin/prestations

Les e-mails envoyes en local (confirmation de commande, formulaire de
contact) sont interceptes par Mailpit, consultables sur
http://localhost:8025.

## Tests

La base de test doit exister avant la premiere execution :

```
docker compose exec php php bin/console --env=test doctrine:database:create --if-not-exists
docker compose exec php php bin/console --env=test doctrine:migrations:migrate --no-interaction
```

Puis, a chaque execution :

```
docker compose exec php php bin/phpunit
```

## Analyse statique

```
docker compose exec php php bin/console cache:warmup --env=dev
docker compose exec php php vendor/bin/phpstan analyse
```

## Integration continue

Un pipeline GitHub Actions (`.github/workflows/ci.yml`) s'execute a chaque push
et pull request sur `MAIN`/`develop` : installation des dependances, tests
PHPUnit contre une vraie base PostgreSQL, analyse statique PHPStan, et build de
l'image Docker. Un job de deploiement separe existe dans le pipeline
(declenche sur un merge vers `MAIN` ou un tag) mais n'est pas encore branche
sur un serveur reel.

## Organisation du projet

Le code applicatif est un monolithe modulaire sous `src/`, un dossier par
domaine metier : Catalogue, Commande, Promotion, Paiement, User. Chaque
module regroupe ses propres Entity, Repository, Service et Controller, et
n'accede jamais directement aux entites d'un autre module.

La liste des ressources API Platform et le detail des routes seront documentes
au fur et a mesure de leur ajout dans les etapes suivantes.
