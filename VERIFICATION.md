# Vérification de la version simplifiée — 18 octobre 2026

Ce rapport remplace le rapport de l’ancienne interface. Les anciennes captures et browser-workflow-evidence.json sont des archives et ne prouvent pas les parcours de cette version.

## Tests exécutés

| Vérification | Résultat réel |
| --- | --- |
| PHPUnit via le runtime PHP fourni | 30 tests réussis, 336 assertions |
| node backend/tests/http-smoke.mjs | 11 contrôles HTTP réussis, 0 échec |
| npm run build | Réussi ; 107 modules |
| artisan route:list --except-vendor | 15 routes applicatives |
| artisan migrate:status | Migration existante appliquée |
| PHP -l sur app/, routes/, database/, tests/ | 23 fichiers sans erreur de syntaxe |
| composer check-platform-reqs | Exigences satisfaites |
| verify-database.php | 50 produits, 8 catégories, 75 mouvements, 1 027 unités, 3 inactifs |

Le build final produit 339,84 kB de JavaScript (109,01 kB gzip) et 4,19 kB de CSS (1,45 kB gzip). Le lancement initial du build était bloqué par la lecture des métadonnées de répertoires parents dans le sandbox Windows ; son exécution autorisée hors sandbox a réussi.

## Couverture API et base de données

Les tests Laravel utilisent une vraie SQLite en mémoire, isolée du fichier de démonstration. Ils couvrent notamment :

- login/logout, restauration de session, requêtes anonymes et identifiants incorrects ;
- interdiction des cinq écritures catalogue pour employé ;
- création produit à zéro, mise à jour partielle, SKU unique et quantité directe interdite ;
- création et renommage de catégorie sans perdre ses produits, nom unique et prix négatif ;
- IN/OUT, auteur provenant de la session, quantités avant/après et historique ;
- stock insuffisant, quantité zéro/négative/décimale, produit inactif ;
- rollback si insertion historique impossible ;
- plafond de stock et rejet SQLite d’une modification négative directe ;
- historique immuable, absence de routes d’édition/suppression et clés étrangères ;
- recherche, filtres, pagination, statistiques dashboard ;
- migrations, 50 produits/8 catégories/75 mouvements, équilibrage complet de l’historique et Seeder idempotent.

Le script HTTP complète les tests Laravel par de vrais échanges avec localhost:8000 : cookies Session et XSRF, CORS avec credentials, réponse 419 sans en-tête CSRF, login employé, profil, cinq réponses 403 et invalidation de Session après logout.

## Vérification navigateur de cette version

Contrôles effectués dans le navigateur intégré :

- restauration de la session administrateur et dashboard avec 50 produits, 1 027 unités, 10 stocks faibles et 5 ruptures ;
- recherche Samsung : 4 résultats ; filtre SSD & Stockage combiné : 2 résultats ;
- réinitialisation et passage en page 2 sur 5 ;
- liste des 8 catégories et présence du formulaire administrateur ;
- fiche du produit IT-0013 avec quantité 39, champs administrateur et historique récent ;
- lien vers mouvement avec produit présélectionné ;
- OUT de 40 sur un stock de 39 : message de stock insuffisant ;
- OUT de 3 : preview 39 → 36 et confirmation inline, puis annulation sans enregistrement ;
- ouverture de l’historique et sélection du filtre OUT ;
- logout et retour à Login ;
- connexion employé réussie, accès à /products/new redirigé vers /products, champ produit désactivé et bouton d’enregistrement absent.

Les écritures catalogue et stock ont été validées dans les tests SQLite isolés. Aucun mouvement ni produit de test n’a été ajouté à la base de démonstration.

## Responsive et design

Inspection à 1 280 × 900 et à 390 × 844. À 390 px, body.scrollWidth = 375 px ; le conteneur de table mesure 341 px et conserve sa table de 662 px dans un défilement horizontal interne. La navigation revient à la ligne et les filtres s’empilent.

Le détecteur Impeccable a signalé un avertissement esthétique sur Arial. La police système est volontaire et correspond à la demande d’une interface étudiante simple. Cet avertissement ne représente pas un échec fonctionnel. Aucune nouvelle police n’a été ajoutée.

Un ancien cache de module du serveur Vite affichait une erreur Loading pendant la refactorisation. L’invalidation du module et le rechargement ont rétabli l’interface ; le build final est réussi.

## Données préservées et limites

Les compteurs du fichier SQLite sont identiques avant et après la refactorisation. Aucun migrate:fresh ni reset de la démonstration n’a été exécuté. Les migrations destructives de RefreshDatabase s’exécutent uniquement en mémoire.

Pas de test de charge concurrente, pas de validation exhaustive sur appareils physiques, ni de suite de tests navigateur automatisée. La protection contre les accès concurrents conserve la mise à jour SQL conditionnelle et la Transaction ; sa tenue sous forte charge n’est pas revendiquée.

L’historique propose les 100 premiers produits dans son filtre. Le formulaire de mouvement recherche côté serveur et limite chaque résultat à 100 références.

