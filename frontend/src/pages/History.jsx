import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { Loading, ErrorState, Pagination, date } from "../components/UI";

export default function History() {
  const [params, setParams] = useSearchParams();
  const [movements, setMovements] = useState(null);
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [retry, setRetry] = useState(0);
  const query = params.toString();

  useEffect(() => {
    const controller = new AbortController();
    async function loadHistory() {
      setLoading(true);
      setError("");
      try {
        const [movementResponse, productResponse] = await Promise.all([
          api.get("/api/movements?" + query, { signal: controller.signal }),
          api.get("/api/products?per_page=100", { signal: controller.signal }),
        ]);
        setMovements(movementResponse.data);
        setProducts(productResponse.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadHistory();
    return () => controller.abort();
  }, [query, retry]);

  function updateFilter(name, value) {
    const next = new URLSearchParams(params);
    if (value) next.set(name, value);
    else next.delete(name);
    next.delete("page");
    setParams(next);
  }

  function changePage(page) {
    const next = new URLSearchParams(params);
    next.set("page", page);
    setParams(next);
  }

  return (
    <>
      <div className="page-heading"><h1>Historique des mouvements</h1><Link className="button primary" to="/movements/new">Nouveau mouvement</Link></div>
      <p>Les mouvements sont conservés et ne peuvent pas être modifiés ou supprimés.</p>
      <div className="filters">
        <label>Produit
          <select value={params.get("product_id") || ""} onChange={(event) => updateFilter("product_id", event.target.value)}>
            <option value="">Tous les produits</option>
            {products.map((product) => <option key={product.id} value={product.id}>{product.name} ({product.sku})</option>)}
          </select>
        </label>
        <label>Type
          <select value={params.get("type") || ""} onChange={(event) => updateFilter("type", event.target.value)}>
            <option value="">Tous les mouvements</option><option value="IN">Entrées</option><option value="OUT">Sorties</option>
          </select>
        </label>
        <button onClick={() => setParams({})}>Réinitialiser</button>
      </div>
      {loading && <Loading />}
      {error && <ErrorState message={error} retry={() => setRetry(retry + 1)} />}
      {!loading && !error && movements && (
        <>
          <div className="table-wrap">
            <table>
              <caption>Mouvements, du plus récent au plus ancien</caption>
              <thead><tr><th>Date</th><th>Produit / SKU</th><th>Type</th><th>Quantité</th><th>Avant → Après</th><th>Utilisateur</th><th>Note</th></tr></thead>
              <tbody>
                {movements.data.map((movement) => (
                  <tr key={movement.id}>
                    <td>{date(movement.created_at)}</td>
                    <td><Link to={"/products/" + movement.product_id}>{movement.product.name}</Link><br />{movement.product.sku}</td>
                    <td>{movement.type === "IN" ? "Entrée" : "Sortie"}</td><td>{movement.quantity}</td>
                    <td>{movement.quantity_before} → {movement.quantity_after}</td><td>{movement.user.name}</td><td>{movement.note || "—"}</td>
                  </tr>
                ))}
                {movements.data.length === 0 && <tr><td colSpan="7">Aucun mouvement trouvé.</td></tr>}
              </tbody>
            </table>
          </div>
          <Pagination data={movements} onPage={changePage} />
        </>
      )}
    </>
  );
}

