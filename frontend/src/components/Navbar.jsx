import { useState } from "react";
import { Link, NavLink, Outlet } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { errorMessage } from "../api/api";

export default function Navbar() {
  const { user, logout, isAdmin } = useAuth();
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  async function handleLogout() {
    setBusy(true);
    setError("");
    try {
      await logout();
    } catch (error) {
      setError(errorMessage(error));
    } finally {
      setBusy(false);
    }
  }

  return (
    <>
      <a className="skip-link" href="#main-content">Aller au contenu</a>
      <header className="navbar">
        <div className="navbar-top">
          <Link className="brand" to="/">IT Stock Manager</Link>
          <div className="actions">
            <span>{user.name} · {isAdmin ? "Administrateur" : "Employé"}</span>
            <button disabled={busy} onClick={handleLogout}>{busy ? "Déconnexion…" : "Se déconnecter"}</button>
          </div>
        </div>
        <nav aria-label="Navigation principale">
          <NavLink end to="/">Tableau de bord</NavLink>
          <NavLink to="/products">Produits</NavLink>
          <NavLink to="/categories">Catégories</NavLink>
          <NavLink to="/movements/new">Mouvement de stock</NavLink>
          <NavLink to="/history">Historique</NavLink>
        </nav>
        {error && <p role="alert" className="error">{error}</p>}
      </header>
      <main id="main-content"><Outlet /></main>
    </>
  );
}

