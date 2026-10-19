import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { useAuth } from "../context/AuthContext";
import { Loading, ErrorState } from "../components/UI";

export default function Categories() {
  const { isAdmin } = useAuth();
  const [categories, setCategories] = useState([]);
  const [editingId, setEditingId] = useState(null);
  const [name, setName] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [loadError, setLoadError] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [retry, setRetry] = useState(0);

  useEffect(() => {
    const controller = new AbortController();
    async function loadCategories() {
      setLoading(true);
      setLoadError("");
      try {
        const response = await api.get("/api/categories", { signal: controller.signal });
        setCategories(response.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setLoadError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadCategories();
    return () => controller.abort();
  }, [retry]);

  async function handleSubmit(event) {
    event.preventDefault();
    setBusy(true);
    setError("");
    setMessage("");
    try {
      if (editingId) {
        const response = await api.patch("/api/categories/" + editingId, { name });
        setCategories(categories.map((category) => category.id === editingId ? response.data.data : category));
        setMessage("Catégorie modifiée.");
      } else {
        const response = await api.post("/api/categories", { name });
        setCategories([...categories, response.data.data]);
        setMessage("Catégorie ajoutée.");
      }
      setName("");
      setEditingId(null);
    } catch (error) {
      setError(errorMessage(error));
    } finally {
      setBusy(false);
    }
  }

  return (
    <>
      <h1>Catégories</h1>
      {message && <p className="success" role="status">{message}</p>}
      {isAdmin && (
        <form className="panel" onSubmit={handleSubmit}>
          <h2>{editingId ? "Modifier la catégorie" : "Ajouter une catégorie"}</h2>
          <fieldset disabled={busy}>
            <label>Nom de la catégorie
              <input required maxLength={100} value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            {error && <p className="error" role="alert">{error}</p>}
            <div className="actions">
              <button className="primary">{busy ? "Enregistrement…" : "Enregistrer"}</button>
              {editingId && <button type="button" onClick={() => { setEditingId(null); setName(""); setError(""); }}>Annuler</button>}
            </div>
          </fieldset>
        </form>
      )}
      {loading && <Loading />}
      {loadError && <ErrorState message={loadError} retry={() => setRetry(retry + 1)} />}
      {!loading && !loadError && (
        <div className="table-wrap">
          <table>
            <caption>Liste des catégories</caption>
            <thead><tr><th>Nom</th><th>Nombre de produits</th><th>Actions</th></tr></thead>
            <tbody>
              {categories.map((category) => (
                <tr key={category.id}>
                  <td>{category.name}</td><td>{category.products_count}</td>
                  <td><div className="actions">
                    <Link to={"/products?category_id=" + category.id}>Voir les produits</Link>
                    {isAdmin && <button disabled={busy} onClick={() => { setEditingId(category.id); setName(category.name); setError(""); setMessage(""); }}>Modifier</button>}
                  </div></td>
                </tr>
              ))}
              {categories.length === 0 && <tr><td colSpan="3">Aucune catégorie.</td></tr>}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

