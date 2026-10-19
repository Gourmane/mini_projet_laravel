import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import api, { errorMessage } from "../api/api";
import { useAuth } from "../context/AuthContext";
import { Loading, ErrorState, Pagination } from "../components/UI";
import ProductCard from "../components/ProductCard";

export default function Products() {
  const { isAdmin } = useAuth();
  const [params, setParams] = useSearchParams();
  const [search, setSearch] = useState(params.get("search") || "");
  const [products, setProducts] = useState(null);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [retry, setRetry] = useState(0);
  const query = params.toString();

  useEffect(() => {
    setSearch(params.get("search") || "");
  }, [params]);

  useEffect(() => {
    const controller = new AbortController();
    async function loadProducts() {
      setLoading(true);
      setError("");
      try {
        const [productResponse, categoryResponse] = await Promise.all([
          api.get("/api/products?" + query, { signal: controller.signal }),
          api.get("/api/categories", { signal: controller.signal }),
        ]);
        setProducts(productResponse.data);
        setCategories(categoryResponse.data.data);
      } catch (error) {
        if (!controller.signal.aborted) setError(errorMessage(error));
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    }
    loadProducts();
    // Cancel the previous request when filters change or the page closes.
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
      <div className="page-heading">
        <h1>Produits</h1>
        {isAdmin && <Link className="button primary" to="/products/new">Ajouter un produit</Link>}
      </div>
      <form className="filters" onSubmit={(event) => { event.preventDefault(); updateFilter("search", search); }}>
        <label>Rechercher
          <input type="search" placeholder="Nom, marque ou SKU" value={search} onChange={(event) => setSearch(event.target.value)} />
        </label>
        <button type="submit">Rechercher</button>
        <label>Catégorie
          <select value={params.get("category_id") || ""} onChange={(event) => updateFilter("category_id", event.target.value)}>
            <option value="">Toutes les catégories</option>
            {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
          </select>
        </label>
        <label>État du stock
          <select value={params.get("stock_status") || ""} onChange={(event) => updateFilter("stock_status", event.target.value)}>
            <option value="">Tous les états</option>
            <option value="healthy">En stock</option>
            <option value="low">Stock faible</option>
            <option value="out">Rupture</option>
            <option value="inactive">Inactif</option>
          </select>
        </label>
        <button type="button" onClick={() => { setSearch(""); setParams({}); }}>Réinitialiser</button>
      </form>
      {loading && <Loading />}
      {error && <ErrorState message={error} retry={() => setRetry(retry + 1)} />}
      {!loading && !error && products && (
        <>
          {products.data.length === 0 ? (
            <p className="panel">Aucun produit trouvé. Modifiez les filtres.</p>
          ) : (
            <div className="product-grid" aria-label="Catalogue des produits">
              {products.data.map((product) => <ProductCard key={product.id} product={product} returnTo={"/products" + (query ? "?" + query : "")} />)}
            </div>
          )}
          <Pagination data={products} onPage={changePage} />
        </>
      )}
      <p className="muted">Les quantités se modifient uniquement par un mouvement de stock.</p>
    </>
  );
}

