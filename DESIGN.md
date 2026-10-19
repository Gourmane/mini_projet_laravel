# IT Stock Manager — interface pédagogique

Interface française de projet étudiant : fond clair, navigation horizontale simple, police système Arial, un bleu pour les liens et actions principales, couleurs de statut accompagnées de texte.

Le dashboard contient quatre cartes et une table de mouvements récents. Les produits utilisent des cartes simples en CSS Grid : quatre colonnes sur grand écran, trois jusqu’à 1100 px, deux jusqu’à 900 px et une jusqu’à 540 px. Chaque carte présente l’image, le nom, la marque, la référence, la catégorie, le prix, le stock, son statut et un lien vers la fiche. Les catégories et l’historique conservent leurs tables HTML.

La fiche produit présente l’image et les informations en lecture seule dans un panneau à deux colonnes, empilées jusqu’à 700 px. Le bouton « Modifier », réservé à l’administrateur, ouvre le formulaire existant. La création et la modification acceptent une URL d’image HTTP ou HTTPS avec aperçu ; une image SVG locale remplace les images absentes ou impossibles à charger. Les cartes et panneaux gardent le fond blanc, les bordures claires, les angles de 4 px et les contrôles bleus existants. Les mouvements récents et le lien vers tout l’historique restent présents ; les quantités se modifient uniquement par un mouvement de stock.

Les formulaires sont ordinaires et les messages restent dans leur page. La confirmation de stock est affichée dans le formulaire, sans modal.

Tout le CSS est dans frontend/src/styles/style.css. Aucun package UI, icône, police téléchargée, graphique ou animation. Les tables défilent dans leur conteneur sur téléphone. Les liens de navigation reviennent à la ligne. Les labels, focus visibles, messages accessibles et contrôles désactivés restent présents.

