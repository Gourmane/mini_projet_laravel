# IT Stock Manager

Projet Full Stack pédagogique : React en JavaScript, Laravel, Sanctum et SQLite. Interface française simple pour les produits, catégories, entrées/sorties et historique.

## Démarrer sur cet ordinateur

Depuis le dossier `it-stock-manager`, ouvrir deux terminaux PowerShell :

```powershell
.\start-backend.ps1
```

Dans le second terminal :

```powershell
.\start-frontend.ps1
```

Ouvrir [l’application locale](http://localhost:5173). Laravel écoute sur le port 8000. Utiliser `localhost` pour les deux serveurs. PHP portable et Composer sont fournis dans `tools/`.

| Rôle | E-mail | Mot de passe |
| --- | --- | --- |
| Administrateur | admin@demo.test | Admin123! |
| Employé | employee@demo.test | Employee123! |

Ces identifiants publics servent uniquement à la démonstration locale.

## Comprendre le code

Commencer par [BEGINNER_GUIDE.md](BEGINNER_GUIDE.md), en arabe simple. Le [rapport de refactorisation](REFACTORING.md) explique les suppressions, les choix et les fichiers à étudier. Les résultats exécutés sont dans [VERIFICATION.md](VERIFICATION.md).

```text
frontend/src/
  api/api.js
  components/Navbar.jsx, UI.jsx
  context/AuthContext.jsx
  pages/Login.jsx, Dashboard.jsx, Products.jsx, ProductDetail.jsx
        Categories.jsx, MovementNew.jsx, History.jsx
  styles/style.css
  App.jsx
  main.jsx
backend/
  app/Http/Controllers/
  app/Http/Requests/
  app/Http/Middleware/AdminOnly.php
  app/Models/
  app/Services/StockService.php
  database/migrations/
  database/seeders/DatabaseSeeder.php
  routes/api.php, web.php
  tests/Feature/InventoryTest.php
```

Chaque page utilise useState et des appels Axios directs. Les composants proches communiquent par props. Seule l’authentification est partagée dans Context. Aucun Redux, Redux Toolkit, Zustand, reducer ou hook générique de chargement.

## Fonctionnalités et droits

Les deux rôles consultent le dashboard, recherchent et filtrent le catalogue, consultent les catégories et l’historique paginé, et enregistrent IN/OUT. L’administrateur ajoute et modifie les produits et catégories. Un produit peut être désactivé ; ses quantités et son historique sont conservés. Il n’existe pas de suppression définitive du catalogue.

Les nouveaux produits commencent à zéro. Seul un mouvement change la quantité. Laravel contrôle les rôles, les SKU uniques, les quantités et les produits inactifs. StockService effectue une mise à jour conditionnelle et l’écriture de l’historique dans une Transaction. SQLite refuse le stock négatif et les modifications/suppressions de mouvements.

Les 50 produits d’origine, 8 catégories, 75 mouvements et 1 027 unités de démonstration ont été conservés. Le Seeder utilise firstOrCreate : le relancer ne remet pas les stocks à zéro. Les tests exécutent les migrations et le Seeder dans une SQLite en mémoire.

## Installation sur une nouvelle copie

Prérequis : PHP 8.4 et ses extensions SQLite, mbstring, openssl, curl, fileinfo, intl, dom et xml ; Composer ; Node.js 22.12+ et npm.

Depuis le dossier du projet, configurer PHP portable si le dossier a changé :

```powershell
.\configure-php.ps1
```

Puis, uniquement pour une installation neuve :

```powershell
cd backend
..\tools\php\php.exe ..\tools\composer.phar install --prefer-dist
Copy-Item .env.example .env
New-Item -ItemType File -Path database/database.sqlite
..\tools\php\php.exe artisan key:generate
..\tools\php\php.exe artisan migrate --seed
```

Ne pas écraser un fichier .env ou SQLite existant. Ne pas exécuter migrate:fresh sur des données à conserver.

Dans un autre terminal, depuis le dossier du projet :

```powershell
cd frontend
npm ci
npm run dev
```

## Vérifier

Depuis backend :

```powershell
..\tools\php\php.exe vendor/phpunit/phpunit/phpunit
..\tools\php\php.exe artisan route:list
..\tools\php\php.exe artisan migrate:status
..\tools\php\php.exe verify-database.php
```

Depuis frontend : `npm run build`. Avec Laravel lancé, depuis le dossier du projet : `node backend/tests/http-smoke.mjs`.

## API et authentification

Axios utilise withCredentials et withXSRFToken. Login : GET /sanctum/csrf-cookie, puis POST /login. Laravel renouvelle l’identifiant de Session. Logout invalide la Session et renouvelle le jeton CSRF. Les erreurs 401/419 renvoient vers Login. Les mots de passe sont hachés ; aucun mot de passe ou Token n’est stocké dans localStorage.

```text
POST /login, /logout
GET /api/user, /api/dashboard
GET, POST /api/products
GET, PATCH /api/products/{product}
PATCH /api/products/{product}/status
GET, POST /api/categories
PATCH /api/categories/{category}
GET, POST /api/movements
```

Les listes paginées retournent data, current_page, last_page et total. Les autres lectures et créations retournent {data: ...}. Les erreurs utilisent message et éventuellement errors. Les créations retournent 201 ; Validation 422 ; accès refusé 403.

Dashboard retourne les quatre compteurs affichés, recent_movements, healthy_stock et movement_count. Les anciennes données décoratives de répartition et de valorisation ne sont plus calculées.

## Images des produits

Les produits ont maintenant un champ `image_url`, des cartes simples et une fiche avec image. L’administrateur utilise **Voir détails → Modifier → URL de l’image → Enregistrer le produit**. Les liens vides ou cassés affichent une image SVG locale.

Sur une installation existante, depuis `backend`, lancer `..\tools\php\php.exe artisan migrate` puis `..\tools\php\php.exe artisan db:seed --class=ProductImageSeeder`. Le seeder remplit uniquement les images vides, sans toucher au stock ni aux mouvements. Les 50 liens sont visibles dans `database/seeders/ProductImageSeeder.php`.

Le guide [PRODUCT_IMAGES.md](PRODUCT_IMAGES.md) explique la fonctionnalité en arabe, liste les fichiers modifiés, les sources des images et les vérifications effectuées.

## Limites

Projet local, sans déploiement ni configuration de production. SQLite sérialise les écritures : trois tentatives bornées gèrent les verrous temporaires, puis Laravel renvoie 503 avec Retry-After. Aucun test de charge concurrente n’est revendiqué.

Le sélecteur d’historique affiche les 100 premiers produits ; le formulaire de mouvement recherche côté serveur et affiche au plus 100 résultats. Le catalogue complet reste paginé. Cette limite convient au MVP de 50 références.

Les captures du dossier screenshots et browser-workflow-evidence.json conservés de la version précédente sont historiques. VERIFICATION.md décrit les vérifications de la version simplifiée ; PRODUCT_IMAGES.md et les fichiers product-image*-checks.json documentent les vérifications de la nouvelle fonctionnalité d’images.

