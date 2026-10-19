import { Navigate, Outlet, Route, Routes, Link } from "react-router-dom";
import { useAuth } from "./context/AuthContext";
import { Loading } from "./components/UI";
import Navbar from "./components/Navbar";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Products from "./pages/Products";
import ProductDetail from "./pages/ProductDetail";
import Categories from "./pages/Categories";
import MovementNew from "./pages/MovementNew";
import History from "./pages/History";
function Protected() {
  const { user, loading } = useAuth();
  if (loading) return <Loading />;
  if (!user) return <Navigate to="/login" replace />;
  return <Outlet />;
}
function Admin() {
  const { isAdmin } = useAuth();
  return isAdmin ? <Outlet /> : <Navigate to="/products" replace />;
}
export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route element={<Protected />}>
        <Route element={<Navbar />}>
          <Route index element={<Dashboard />} />
          <Route path="products" element={<Products />} />
          <Route element={<Admin />}>
            <Route path="products/new" element={<ProductDetail key="new" />} />
          </Route>
          <Route
            path="products/:id"
            element={<ProductDetail key="existing" />}
          />
          <Route path="categories" element={<Categories />} />
          <Route path="movements/new" element={<MovementNew />} />
          <Route path="history" element={<History />} />
          <Route
            path="*"
            element={
              <div className="empty">
                <h1>Page introuvable</h1>
                <Link className="button primary" to="/">
                  Revenir au tableau de bord
                </Link>
              </div>
            }
          />
        </Route>
      </Route>
    </Routes>
  );
}
