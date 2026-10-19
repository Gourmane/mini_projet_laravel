import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { Loading, ErrorState, date } from "../components/UI";

export default function Dashboard() {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [retry, setRetry] = useState(0);

  useEffect(() => {
    const controller = new AbortController();
    async function loadDashboard() {
      setLoading(true);
      setError("");
      try {
        const response = await api.get("/api/dashboard", { signal: controller.signal });
        setDashboard(response.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadDashboard();
    return () => controller.abort();
  }, [retry]);

  if (loading) return <Loading />;
  if (error) return <ErrorState message={error} retry={() => setRetry(retry + 1)} />;

  return (
    <>
      <div className="page-heading">
        <h1>Tableau de bord</h1>
        <Link className="button primary" to="/movements/new">Nouveau mouvement</Link>
      </div>
      <div className="stats">
        <Link to="/products"><span>Total produits</span><strong>{dashboard.total_products}</strong></Link>
        <Link to="/products"><span>Total stock (unités)</span><strong>{dashboard.total_units}</strong></Link>
        <Link to="/products?stock_status=low"><span>Stock faible</span><strong>{dashboard.low_stock}</strong></Link>
        <Link to="/products?stock_status=out"><span>Rupture de stock</span><strong>{dashboard.out_of_stock}</strong></Link>
      </div>
      <h2>Mouvements récents</h2>
      <div className="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Produit</th><th>Type</th><th>Quantité</th><th>Avant → Après</th><th>Utilisateur</th></tr></thead>
          <tbody>
            {dashboard.recent_movements.map((movement) => (
              <tr key={movement.id}>
                <td>{date(movement.created_at)}</td>
                <td><Link to={"/products/" + movement.product_id}>{movement.product.name}</Link></td>
                <td>{movement.type === "IN" ? "Entrée" : "Sortie"}</td><td>{movement.quantity}</td>
                <td>{movement.quantity_before} → {movement.quantity_after}</td><td>{movement.user.name}</td>
              </tr>
            ))}
            {dashboard.recent_movements.length === 0 && <tr><td colSpan="6">Aucun mouvement enregistré.</td></tr>}
          </tbody>
        </table>
      </div>
      <p><Link to="/history">Voir tout l’historique</Link></p>
    </>
  );
}

