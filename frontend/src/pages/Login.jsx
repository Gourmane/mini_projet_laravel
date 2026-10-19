import { useState } from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { errorMessage } from "../api/api";
import { Loading } from "../components/UI";

export default function Login() {
  const { user, loading, login, initialError, restore } = useAuth();
  const [email, setEmail] = useState("admin@demo.test");
  const [password, setPassword] = useState("Admin123!");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  async function handleSubmit(event) {
    event.preventDefault();
    setBusy(true);
    setError("");
    try {
      await login({ email, password });
    } catch (error) {
      setError(errorMessage(error));
    } finally {
      setBusy(false);
    }
  }

  if (loading) return <Loading />;
  if (user) return <Navigate to="/" replace />;

  return (
    <main className="login">
      <h1>IT Stock Manager</h1>
      <p>Connectez-vous pour gérer le stock informatique.</p>
      {initialError && <div className="error" role="alert">{initialError} <button onClick={restore}>Réessayer</button></div>}
      <form onSubmit={handleSubmit}>
        <label>Adresse e-mail
          <input type="email" autoComplete="username" required value={email} onChange={(event) => setEmail(event.target.value)} />
        </label>
        <label>Mot de passe
          <input type="password" autoComplete="current-password" required value={password} onChange={(event) => setPassword(event.target.value)} />
        </label>
        {error && <p className="error" role="alert">{error}</p>}
        <button className="primary" disabled={busy}>{busy ? "Connexion…" : "Se connecter"}</button>
      </form>
      <h2>Comptes de démonstration</h2>
      <p>Administrateur : admin@demo.test / Admin123!</p>
      <p>Employé : employee@demo.test / Employee123!</p>
      <p className="muted">Identifiants publics réservés à ce projet local.</p>
    </main>
  );
}

