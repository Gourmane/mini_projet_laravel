import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000",
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: "application/json" },
});

// Expired sessions must also update the user stored in AuthContext.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 || error.response?.status === 419) {
      window.dispatchEvent(new Event("auth-expired"));
    }
    return Promise.reject(error);
  },
);

export function errorMessage(error) {
  const errors = error.response?.data?.errors;
  if (errors) return Object.values(errors).flat()[0];
  if (error.response?.status === 419) return "Votre session a expiré. Reconnectez-vous.";
  return error.response?.data?.message || "Connexion au serveur impossible. Démarrez Laravel puis réessayez.";
}

export default api;

