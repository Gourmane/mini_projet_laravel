import { Link } from "react-router-dom";
import { StockBadge, imageFallback, productImage } from "./UI";

export default function ProductCard({ product, returnTo }) {
  return (
    <article className="product-card">
      <img className="product-image" src={productImage(product.image_url)} alt={product.name} loading="lazy" onError={imageFallback} />
      <div className="product-card-body">
        <h2>{product.name}</h2>
        <p className="muted">{product.brand || "Marque non renseignée"}</p>
        <p>Réf : {product.sku}</p>
        <p>Catégorie : {product.category.name}</p>
        <p>Prix : <strong>{product.unit_price === null ? "Non renseigné" : Number(product.unit_price).toLocaleString("fr-MA") + " MAD"}</strong></p>
        <p>Stock : {product.quantity} unités</p>
        <p>Statut : <StockBadge product={product} /></p>
        <Link className="button" to={"/products/" + product.id} state={{ returnTo }}>Voir détails</Link>
      </div>
    </article>
  );
}
