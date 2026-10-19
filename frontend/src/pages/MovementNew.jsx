import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { Loading, ErrorState } from "../components/UI";

export default function MovementNew() {
  const [params] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [productId, setProductId] = useState(params.get("product_id") || "");
  const [product, setProduct] = useState(null);
  const [type, setType] = useState("IN");
  const [quantity, setQuantity] = useState("");
  const [note, setNote] = useState("");
  const [search, setSearch] = useState("");
  const [searchTerm, setSearchTerm] = useState("");
  const [loading, setLoading] = useState(true);
  const [productLoading, setProductLoading] = useState(false);
  const [loadError, setLoadError] = useState("");
  const [productError, setProductError] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(null);
  const [confirming, setConfirming] = useState(false);
  const [busy, setBusy] = useState(false);
  const [retry, setRetry] = useState(0);

  useEffect(() => {
    const controller = new AbortController();
    async function loadProducts() {
      setLoading(true);
      setLoadError("");
      try {
        const response = await api.get("/api/products", {
          params: { active: 1, per_page: 100, search: searchTerm },
          signal: controller.signal,
        });
        setProducts(response.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setLoadError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadProducts();
    return () => controller.abort();
  }, [searchTerm, retry]);

  useEffect(() => {
    const controller = new AbortController();
    async function loadSelectedProduct() {
      setProduct(null);
      setProductError("");
      if (!productId) {
        setProductLoading(false);
        return;
      }
      setProductLoading(true);
      try {
        const response = await api.get("/api/products/" + productId, { signal: controller.signal });
        setProduct(response.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setProductError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setProductLoading(false);
      }
    }
    loadSelectedProduct();
    return () => controller.abort();
  }, [productId, retry]);

  let expectedStock = product?.quantity || 0;
  if (type === "IN") expectedStock += Number(quantity);
  else expectedStock -= Number(quantity);

  function reviewMovement(event) {
    event.preventDefault();
    setError("");
    setSuccess(null);
    if (!product || !product.is_active) {
      setError("Choisissez un produit actif.");
      return;
    }
    if (expectedStock < 0) {
      setError("Stock insuffisant pour cette sortie.");
      return;
    }
    setConfirming(true);
  }

  async function recordMovement() {
    setBusy(true);
    setError("");
    try {
      const response = await api.post("/api/movements", {
        product_id: Number(productId),
        type,
        quantity: Number(quantity),
        note: note || null,
      });
      const movement = response.data.data;
      setSuccess(movement);
      setProduct(movement.product);
      setQuantity("");
      setNote("");
    } catch (error) {
      setError(errorMessage(error));
      // Another user may have changed the stock since the preview.
      setRetry(retry + 1);
    } finally {
      setConfirming(false);
      setBusy(false);
    }
  }

  // Keep the current selection visible even when a new search excludes it.
  const options = products.filter((item) => String(item.id) !== String(productId));
  if (product) options.unshift(product);

  return (
    <>
      <div className="page-heading"><h1>Mouvement de stock</h1><Link to="/history">Voir l’historique</Link></div>
      {success && (
        <div className="success" role="status">
          <p>Mouvement enregistré : {success.product.name}, {success.quantity_before} → {success.quantity_after} unités.</p>
          <Link to={"/history?product_id=" + success.product_id}>Voir la trace</Link>
        </div>
      )}
      <form className="filters" onSubmit={(event) => { event.preventDefault(); setSearchTerm(search); }}>
        <label>Rechercher un produit
          <input type="search" placeholder="Nom, marque ou SKU" disabled={confirming || busy} value={search} onChange={(event) => setSearch(event.target.value)} />
        </label>
        <button disabled={confirming || busy}>Rechercher</button>
      </form>
      {loading && <Loading />}
      {loadError && <ErrorState message={loadError} retry={() => setRetry(retry + 1)} />}
      <form className="panel" onSubmit={reviewMovement}>
        <fieldset disabled={busy || confirming}>
          <label>Produit
            <select required value={productId} onChange={(event) => { setProductId(event.target.value); setProduct(null); setError(""); setSuccess(null); }}>
              <option value="">Choisir un produit</option>
              {options.map((item) => <option key={item.id} value={item.id}>{item.name} ({item.sku})</option>)}
            </select>
          </label>
          <label>Type de mouvement
            <select value={type} onChange={(event) => setType(event.target.value)}>
              <option value="IN">Entrée (IN)</option><option value="OUT">Sortie (OUT)</option>
            </select>
          </label>
          <label>Quantité
            <input type="number" required min="1" max="100000000" step="1" value={quantity} onChange={(event) => setQuantity(event.target.value)} />
          </label>
          <label>Note (facultative)
            <textarea maxLength={500} rows={3} value={note} onChange={(event) => setNote(event.target.value)} />
          </label>
          <button className="primary" disabled={!product?.is_active || productLoading || loading || !!loadError || !!productError}>Vérifier le mouvement</button>
        </fieldset>
        {productLoading && <Loading />}
        {productError && <ErrorState message={productError} retry={() => setRetry(retry + 1)} />}
        {product && <p>Stock actuel : <strong>{product.quantity}</strong> · Stock prévu : <strong>{expectedStock}</strong></p>}
        {product && !product.is_active && <p className="error">Ce produit est inactif.</p>}
        {error && <p className="error" role="alert">{error}</p>}
        {confirming && (
          <section className="confirmation" aria-label="Confirmation du mouvement">
            <p>Confirmer {type === "IN" ? "l’entrée" : "la sortie"} de {quantity} unités pour <strong>{product.name}</strong> ?</p>
            <p>Le stock réel est vérifié par Laravel. Ce mouvement sera conservé dans l’historique.</p>
            <div className="actions">
              <button type="button" className="primary" disabled={busy} onClick={recordMovement}>{busy ? "Enregistrement…" : "Confirmer le mouvement"}</button>
              <button type="button" disabled={busy} onClick={() => setConfirming(false)}>Annuler</button>
            </div>
          </section>
        )}
      </form>
    </>
  );
}

