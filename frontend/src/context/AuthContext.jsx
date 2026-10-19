import { createContext, useContext, useEffect, useState } from "react";
import api from "../api/api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [initialError, setInitialError] = useState("");

  async function restore() {
    setLoading(true);
    setInitialError("");
    try {
      const response = await api.get("/api/user");
      setUser(response.data.data);
    } catch (error) {
      setUser(null);
      if (error.response?.status !== 401 && error.response?.status !== 419) {
        setInitialError("Le serveur est injoignable. Démarrez Laravel puis réessayez.");
      }
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    restore();
    function handleExpiredSession() {
      setUser(null);
    }
    window.addEventListener("auth-expired", handleExpiredSession);
    return () => window.removeEventListener("auth-expired", handleExpiredSession);
  }, []);

  async function login(credentials) {
    await api.get("/sanctum/csrf-cookie");
    const response = await api.post("/login", credentials);
    setUser(response.data.data);
    setInitialError("");
  }

  async function logout() {
    await api.post("/logout");
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, loading, initialError, restore, login, logout, isAdmin: user?.role === "admin" }}>
      {children}
    </AuthContext.Provider>
  );
}

// A small convenience for reading the one shared Context.
export function useAuth() {
  return useContext(AuthContext);
}

