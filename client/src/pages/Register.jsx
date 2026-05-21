import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Layout from '../components/Layout';
import Alert from '../components/Alert';
import { useAuth } from '../context/AuthContext';

const Register = () => {
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'elderly',
    phone: '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (form.password !== form.confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    setLoading(true);
    try {
      const { confirmPassword, ...data } = form;
      const user = await register(data);
      if (user.role === 'admin') navigate('/dashboard');
      else if (user.role === 'caregiver') navigate('/caregiver-dashboard');
      else navigate('/elder-profile');
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Layout>
      <div className="mx-auto max-w-md">
        <form onSubmit={handleSubmit} className="card space-y-4">
          <h1 className="text-2xl font-bold text-slate-800">Create Account</h1>
          <p className="text-sm text-slate-500">Join the ElderCare platform</p>

          <Alert message={error} onClose={() => setError('')} />

          <div>
            <label className="label">Full Name</label>
            <input
              name="name"
              className="input-field"
              value={form.name}
              onChange={handleChange}
              required
            />
          </div>

          <div>
            <label className="label">Email</label>
            <input
              name="email"
              type="email"
              className="input-field"
              value={form.email}
              onChange={handleChange}
              required
            />
          </div>

          <div>
            <label className="label">Phone</label>
            <input
              name="phone"
              className="input-field"
              value={form.phone}
              onChange={handleChange}
            />
          </div>

          <div>
            <label className="label">Role</label>
            <select
              name="role"
              className="input-field"
              value={form.role}
              onChange={handleChange}
            >
              <option value="elderly">Elderly User</option>
              <option value="caregiver">Caregiver</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div>
            <label className="label">Password</label>
            <input
              name="password"
              type="password"
              className="input-field"
              value={form.password}
              onChange={handleChange}
              required
              minLength={6}
            />
          </div>

          <div>
            <label className="label">Confirm Password</label>
            <input
              name="confirmPassword"
              type="password"
              className="input-field"
              value={form.confirmPassword}
              onChange={handleChange}
              required
            />
          </div>

          <button type="submit" className="btn-primary w-full" disabled={loading}>
            {loading ? 'Creating account...' : 'Register'}
          </button>

          <p className="text-center text-sm text-slate-500">
            Already have an account?{' '}
            <Link to="/login" className="font-medium text-primary-600 hover:underline">
              Sign In
            </Link>
          </p>
        </form>
      </div>
    </Layout>
  );
};

export default Register;
