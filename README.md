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

## Tests

```
docker compose exec php php bin/phpunit
```

## Organisation du projet

Le code applicatif est un monolithe modulaire sous `src/`, un dossier par
domaine metier : Catalogue, Commande, Promotion, Paiement, User. Chaque
module regroupe ses propres Entity, Repository, Service et Controller, et
n'accede jamais directement aux entites d'un autre module.

La liste des ressources API Platform et le detail des routes seront documentes
au fur et a mesure de leur ajout dans les etapes suivantes.
