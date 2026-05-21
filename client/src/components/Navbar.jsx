import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import logo from '../assets/logo.png';

const Navbar = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const dashboardPath =
    user?.role === 'admin'
      ? '/dashboard'
      : user?.role === 'caregiver'
        ? '/caregiver-dashboard'
        : '/elder-profile';

  return (
    <nav className="border-b border-slate-200 bg-white shadow-sm">
      <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <Link to="/" className="flex items-center gap-2">
          <span className="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg">
            <img
              src={logo}
              alt="ElderCare logo"
              className="h-full w-full scale-[2] object-contain"
            />
          </span>
          <span className="text-lg font-bold text-slate-800">ElderCare</span>
        </Link>

        <div className="hidden items-center gap-6 md:flex">
          <Link to="/" className="text-sm font-medium text-slate-600 hover:text-primary-600">
            Home
          </Link>
          {user ? (
            <>
              <Link
                to={dashboardPath}
                className="text-sm font-medium text-slate-600 hover:text-primary-600"
              >
                Dashboard
              </Link>
              {user.role === 'admin' && (
                <Link
                  to="/appointments"
                  className="text-sm font-medium text-slate-600 hover:text-primary-600"
                >
                  Appointments
                </Link>
              )}
              {user.role === 'elderly' && (
                <>
                  <Link
                    to="/elder-profile"
                    className="text-sm font-medium text-slate-600 hover:text-primary-600"
                  >
                    My Profile
                  </Link>
                  <Link
                    to="/appointments"
                    className="text-sm font-medium text-slate-600 hover:text-primary-600"
                  >
                    Appointments
                  </Link>
                </>
              )}
              {user.role === 'caregiver' && (
                <Link
                  to="/caregiver-dashboard"
                  className="text-sm font-medium text-slate-600 hover:text-primary-600"
                >
                  Caregiver Panel
                </Link>
              )}
              <span className="rounded-full bg-primary-50 px-3 py-1 text-xs font-medium capitalize text-primary-700">
                {user.role}
              </span>
              <button onClick={handleLogout} className="btn-secondary text-sm">
                Logout
              </button>
            </>
          ) : (
            <>
              <Link to="/login" className="text-sm font-medium text-slate-600 hover:text-primary-600">
                Login
              </Link>
              <Link to="/register" className="btn-primary text-sm">
                Register
              </Link>
            </>
          )}
        </div>

        {user && (
          <div className="md:hidden">
            <button onClick={handleLogout} className="btn-secondary text-sm">
              Logout
            </button>
          </div>
        )}
      </div>
    </nav>
  );
};

export default Navbar;
