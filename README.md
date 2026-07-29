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

## Deploiement en production

Meme image Docker qu'en dev, avec une configuration Nginx et des variables
d'environnement dediees. Le fichier `compose.override.yaml` (bind mount du
code, Mailpit) est fusionne automatiquement par `docker compose up` en dev et
ne doit surtout pas l'etre en production : la commande passe donc par `-f` de
maniere explicite.

Sur le serveur (VPS), avec un checkout du depot :

```
cp .env.example .env
```

Puis creer un `.env.prod.local` (non commite) avec au minimum :

```
APP_SECRET=<secret genere aleatoirement>
POSTGRES_PASSWORD=<mot de passe reel>
MAILER_DSN=<un vrai relais SMTP, pas Mailpit>
```

Et editer `compose.prod.yaml` pour remplacer `DEFAULT_URI` par le vrai nom de
domaine. Puis :

```
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
docker compose -f compose.yaml -f compose.prod.yaml exec php php bin/console doctrine:database:create
docker compose -f compose.yaml -f compose.prod.yaml exec php php bin/console doctrine:migrations:migrate --no-interaction
```

Le conteneur `nginx` sert alors sur le port 80, avec la configuration
`docker/nginx/prod.conf.d` (en-tetes de securite, compression, cache long sur
les assets versionnes par AssetMapper). Le TLS n'est pas configure dans ce
fichier : sans nom de domaine reel a ce stade, un bloc HTTPS non teste serait
plus trompeur qu'utile. A ajouter le moment venu, par exemple via Certbot
(Let's Encrypt) devant Nginx, ou un reverse proxy/CDN en amont du VPS qui
termine le TLS.

## Organisation du projet

Le code applicatif est un monolithe modulaire sous `src/`, un dossier par
domaine metier : Catalogue, Commande, Promotion, Paiement, User. Chaque
module regroupe ses propres Entity, Repository, Service et Controller, et
n'accede jamais directement aux entites d'un autre module.

### Vitrine et back office (Twig)

| Route | Description |
| --- | --- |
| `/`, `/galerie`, `/a-propos`, `/contact` | Pages publiques de la vitrine |
| `/prestations` | Catalogue public, pagination et filtres |
| `/panier`, `/tunnel` | Panier et tunnel de commande (ROLE_USER) |
| `/compte` | Espace client (ROLE_USER) |
| `/admin`, `/admin/prestations` | Back office, CRUD complet sur Prestation (ROLE_ADMIN) |
| `/login`, `/logout` | Authentification |
| `/robots.txt`, `/sitemap.xml` | Genere dynamiquement a partir des routes reelles |

### Ressources API Platform

| Ressource | Operations | Notes |
| --- | --- | --- |
| `Prestation` | GET (collection, item) | Lecture publique, filtres nom/prix |
| `Panier` | GET (`/api/paniers/mine`, item, collection) | `/mine` cree le panier de l'utilisateur connecte a la volee |
| `LignePanier` | POST, GET, PATCH, DELETE | Quantite seule modifiable en PATCH |
| `Commande` | GET (collection, item), POST | Le POST transforme le panier courant en commande |
| `LigneCommande` | GET (collection, item) | Lecture seule, prix fige a l'achat |
| Validation de code promo | `POST /api/promo_codes/valider` | Controleur Symfony classique, pas une ressource API Platform |

Toutes les ressources sont securisees par utilisateur (un client ne voit et ne
modifie que ce qui lui appartient, un admin voit tout) et documentees via
OpenAPI, consultable sur `/api` une fois l'application lancee.
