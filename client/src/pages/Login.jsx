import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Layout from '../components/Layout';
import Alert from '../components/Alert';
import { useAuth } from '../context/AuthContext';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const redirectByRole = (role) => {
    if (role === 'admin') navigate('/dashboard');
    else if (role === 'caregiver') navigate('/caregiver-dashboard');
    else navigate('/elder-profile');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const data = await login(email, password);
      redirectByRole(data.role);
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Layout>
      <div className="mx-auto max-w-md">
        <div className="card">
          <h1 className="text-2xl font-bold text-slate-800">Welcome Back</h1>
          <p className="mt-1 text-sm text-slate-500">Sign in to your ElderCare account</p>

          <Alert message={error} onClose={() => setError('')} />

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <label className="label">Email</label>
              <input
                type="email"
                className="input-field"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                placeholder="you@example.com"
              />
            </div>
            <div>
              <label className="label">Password</label>
              <input
                type="password"
                className="input-field"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                placeholder="••••••••"
              />
            </div>
            <button type="submit" className="btn-primary w-full" disabled={loading}>
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <p className="mt-6 text-center text-sm text-slate-500">
            Don&apos;t have an account?{' '}
            <Link to="/register" className="font-medium text-primary-600 hover:underline">
              Register
            </Link>
          </p>
        </div>
      </div>
    </Layout>
  );
};

export default Login;
