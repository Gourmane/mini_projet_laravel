import { useEffect, useState } from "react";
import { Link, useLocation, useNavigate, useParams } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { useAuth } from "../context/AuthContext";
import { Loading, ErrorState, StockBadge, date, imageFallback, productImage } from "../components/UI";

const emptyForm = {
  name: "",
  sku: "",
  brand: "",
  image_url: "",
  category_id: "",
  unit_price: "",
  min_stock: 0,
  is_active: true,
};

export default function ProductDetail() {
  const { id } = useParams();
  const isNew = !id;
  const { isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [product, setProduct] = useState(null);
  const [categories, setCategories] = useState([]);
  const [form, setForm] = useState(emptyForm);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [loadError, setLoadError] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState(location.state?.message || "");
  const [retry, setRetry] = useState(0);
  const [editing, setEditing] = useState(isNew);
  const returnTo = location.state?.returnTo || "/products";

  useEffect(() => {
    const controller = new AbortController();
    async function loadProduct() {
      setLoading(true);
      setLoadError("");
      setError("");
      setEditing(!id);
      try {
        const categoryResponse = await api.get("/api/categories", { signal: controller.signal });
        setCategories(categoryResponse.data.data);
        if (id) {
          const response = await api.get("/api/products/" + id, { signal: controller.signal });
          const loadedProduct = response.data.data;
          setProduct(loadedProduct);
          setForm({
            name: loadedProduct.name,
            sku: loadedProduct.sku,
            brand: loadedProduct.brand || "",
            image_url: loadedProduct.image_url || "",
            category_id: loadedProduct.category_id,
            unit_price: loadedProduct.unit_price ?? "",
            min_stock: loadedProduct.min_stock,
            is_active: loadedProduct.is_active,
          });
        } else {
          setProduct(null);
          setForm(emptyForm);
        }
      } catch (error) {
        if (!controller.signal.aborted) setLoadError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadProduct();
    return () => controller.abort();
  }, [id, retry]);

  function updateField(name, value) {
    setForm({ ...form, [name]: value });
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setBusy(true);
    setError("");
    setMessage("");
    const payload = {
      ...form,
      image_url: form.image_url.trim() || null,
      category_id: Number(form.category_id),
      unit_price: form.unit_price === "" ? null : Number(form.unit_price),
      min_stock: Number(form.min_stock),
    };
    try {
      if (isNew) {
        const response = await api.post("/api/products", payload);
        navigate("/products/" + response.data.data.id, {
          replace: true,
          state: { message: "Produit créé avec un stock initial de zéro." },
        });
      } else {
        const response = await api.patch("/api/products/" + id, payload);
        setProduct({ ...product, ...response.data.data });
        setEditing(false);
        setMessage("Produit modifié.");
      }
    } catch (error) {
      setError(errorMessage(error));
    } finally {
      setBusy(false);
    }
  }

  if (loading) return <Loading />;
  if (loadError) return <ErrorState message={loadError} retry={() => setRetry(retry + 1)} />;

  return (
    <>
      <p><Link to={returnTo}>Retour aux produits</Link></p>
      <h1>{isNew ? "Ajouter un produit" : "Fiche produit"}</h1>
      {message && <p className="success" role="status">{message}</p>}
      {product && (
        <section className="panel product-detail">
          <img key={product.image_url} className="product-image product-detail-image" src={productImage(product.image_url)} alt={product.name} onError={imageFallback} />
          <div>
            <h2>{product.name}</h2>
            <dl className="product-facts">
              <dt>Marque</dt><dd>{product.brand || "Non renseignée"}</dd>
              <dt>Référence</dt><dd>{product.sku}</dd>
              <dt>Catégorie</dt><dd>{product.category.name}</dd>
              <dt>Prix unitaire</dt><dd>{product.unit_price === null ? "Non renseigné" : Number(product.unit_price).toLocaleString("fr-MA") + " MAD"}</dd>
              <dt>Stock actuel</dt><dd>{product.quantity} unités</dd>
              <dt>Stock minimum</dt><dd>{product.min_stock} unités</dd>
              <dt>Statut du stock</dt><dd><StockBadge product={{ ...product, is_active: true }} /></dd>
              <dt>Produit</dt><dd>{product.is_active ? "Actif" : "Inactif"}</dd>
            </dl>
            <p>La quantité se modifie uniquement par un mouvement de stock.</p>
            <div className="actions">
              {isAdmin && !editing && <button onClick={() => setEditing(true)}>Modifier</button>}
              {product.is_active && <Link className="button" to={"/movements/new?product_id=" + id}>Nouveau mouvement</Link>}
            </div>
            {!product.is_active && <p>Produit inactif : les nouveaux mouvements sont bloqués.</p>}
          </div>
        </section>
      )}
      {isAdmin && editing && <form className="panel" onSubmit={handleSubmit}>
        {!isNew && <h2>Modifier le produit</h2>}
        <fieldset disabled={!isAdmin || busy}>
          <div className="form-grid">
            <label>Nom du produit
              <input required maxLength={200} value={form.name} onChange={(event) => updateField("name", event.target.value)} />
            </label>
            <label>SKU
              <input required maxLength={80} value={form.sku} onChange={(event) => updateField("sku", event.target.value)} />
            </label>
            <label>Marque
              <input maxLength={100} value={form.brand} onChange={(event) => updateField("brand", event.target.value)} />
            </label>
            <label>Catégorie
              <select required value={form.category_id} onChange={(event) => updateField("category_id", event.target.value)}>
                <option value="">Choisir une catégorie</option>
                {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
              </select>
            </label>
            <label>Prix unitaire (MAD)
              <input type="number" min="0" max="99999999.99" step="0.01" value={form.unit_price} onChange={(event) => updateField("unit_price", event.target.value)} />
            </label>
            <label>Seuil de stock minimum
              <input type="number" required min="0" max="100000000" step="1" value={form.min_stock} onChange={(event) => updateField("min_stock", event.target.value)} />
            </label>
          </div>
          <label>URL de l’image
            <input type="url" maxLength={2048} pattern="[hH][tT][tT][pP][sS]?://.+" placeholder="https://…" value={form.image_url} onChange={(event) => updateField("image_url", event.target.value)} aria-describedby="image-help" />
          </label>
          <p id="image-help" className="muted">Lien HTTP ou HTTPS uniquement. Laissez vide pour utiliser l’image par défaut.</p>
          <img key={form.image_url} className="product-image product-preview" src={productImage(form.image_url)} alt={"Aperçu : " + (form.name || "nouveau produit")} onError={imageFallback} />
          <label className="checkbox">
            <input type="checkbox" checked={form.is_active} onChange={(event) => updateField("is_active", event.target.checked)} />
            Produit actif (autorise les mouvements)
          </label>
          <div className="actions">
            <button className="primary">{busy ? "Enregistrement…" : "Enregistrer le produit"}</button>
            {!isNew && <button type="button" onClick={() => { setEditing(false); setRetry(retry + 1); }}>Annuler</button>}
          </div>
        </fieldset>
        {error && <p className="error" role="alert">{error}</p>}
      </form>}
      {product && (
        <>
          <h2>Mouvements récents</h2>
          <div className="table-wrap">
            <table>
              <thead><tr><th>Date</th><th>Type</th><th>Quantité</th><th>Avant → Après</th><th>Utilisateur</th><th>Note</th></tr></thead>
              <tbody>
                {product.movements.map((movement) => (
                  <tr key={movement.id}>
                    <td>{date(movement.created_at)}</td><td>{movement.type === "IN" ? "Entrée" : "Sortie"}</td><td>{movement.quantity}</td>
                    <td>{movement.quantity_before} → {movement.quantity_after}</td><td>{movement.user.name}</td><td>{movement.note || "—"}</td>
                  </tr>
                ))}
                {product.movements.length === 0 && <tr><td colSpan="6">Aucun mouvement pour ce produit.</td></tr>}
              </tbody>
            </table>
          </div>
          <p><Link to={"/history?product_id=" + id}>Voir tout l’historique</Link></p>
        </>
      )}
    </>
  );
}

