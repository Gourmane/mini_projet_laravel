// External images are displayed directly. A missing or broken URL uses one local image.
export function productImage(url) {
  return /^https?:\/\//i.test(url || "") ? url : "/product-placeholder.svg";
}

export function imageFallback(event) {
  if (event.currentTarget.getAttribute("src") !== "/product-placeholder.svg") {
    event.currentTarget.src = "/product-placeholder.svg";
  }
}

export function StockBadge({ product }) {
  let label = "En stock";
  let status = product.stock_status;
  if (!product.is_active) {
    label = "Inactif";
    status = "inactive";
  } else if (status === "out") {
    label = "Rupture";
  } else if (status === "low") {
    label = "Stock faible";
  }
  return <span className={"badge " + status}>{label}</span>;
}

export function Loading() {
  return <p role="status">Chargement des données…</p>;
}

export function ErrorState({ message, retry }) {
  return (
    <div className="error" role="alert">
      <p>{message}</p>
      {retry && <button type="button" onClick={retry}>Réessayer</button>}
    </div>
  );
}

export function Pagination({ data, onPage }) {
  if (!data || data.total === 0) return null;
  return (
    <div className="pagination">
      <span>{data.from}–{data.to} sur {data.total} résultats</span>
      <div className="actions">
        <button disabled={data.current_page <= 1} onClick={() => onPage(data.current_page - 1)}>Précédent</button>
        <span>Page {data.current_page} / {data.last_page}</span>
        <button disabled={data.current_page >= data.last_page} onClick={() => onPage(data.current_page + 1)}>Suivant</button>
      </div>
    </div>
  );
}

export function date(value) {
  return new Date(value).toLocaleString("fr-MA", { timeZone: "Africa/Casablanca" });
}


