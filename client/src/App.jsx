import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import LoadingSpinner from './components/LoadingSpinner';

import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import ElderProfile from './pages/ElderProfile';
import Appointments from './pages/Appointments';
import CaregiverDashboard from './pages/CaregiverDashboard';

const RoleRedirect = () => {
  const { user, loading } = useAuth();
  if (loading) return <LoadingSpinner />;
  if (!user) return <Navigate to="/login" replace />;
  if (user.role === 'admin') return <Navigate to="/dashboard" replace />;
  if (user.role === 'caregiver') return <Navigate to="/caregiver-dashboard" replace />;
  return <Navigate to="/elder-profile" replace />;
};

function App() {
  const { loading } = useAuth();

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <LoadingSpinner message="Starting ElderCare..." />
      </div>
    );
  }

  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/redirect" element={<RoleRedirect />} />

      <Route
        path="/dashboard"
        element={
          <ProtectedRoute roles={['admin']}>
            <Dashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/elder-profile"
        element={
          <ProtectedRoute roles={['elderly']}>
            <ElderProfile />
          </ProtectedRoute>
        }
      />
      <Route
        path="/appointments"
        element={
          <ProtectedRoute roles={['admin', 'elderly', 'caregiver']}>
            <Appointments />
          </ProtectedRoute>
        }
      />
      <Route
        path="/caregiver-dashboard"
        element={
          <ProtectedRoute roles={['caregiver']}>
            <CaregiverDashboard />
          </ProtectedRoute>
        }
      />

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

export default App;
