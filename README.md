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

## Tests

La base de test doit exister avant la premiere execution :

```
docker compose exec php php bin/console --env=test doctrine:database:create --if-not-exists
docker compose exec php php bin/console --env=test doctrine:migrations:migrate --no-interaction
```

Puis, a chaque execution :

```
docker compose exec -e APP_ENV=test php php bin/phpunit
```

Le `-e APP_ENV=test` est necessaire car le conteneur `php` a `APP_ENV=dev`
comme variable d'environnement reelle (voir `compose.yaml`), qui a la
priorite sur la valeur forcee par `phpunit.dist.xml`.

## Organisation du projet

Le code applicatif est un monolithe modulaire sous `src/`, un dossier par
domaine metier : Catalogue, Commande, Promotion, Paiement, User. Chaque
module regroupe ses propres Entity, Repository, Service et Controller, et
n'accede jamais directement aux entites d'un autre module.

La liste des ressources API Platform et le detail des routes seront documentes
au fur et a mesure de leur ajout dans les etapes suivantes.
