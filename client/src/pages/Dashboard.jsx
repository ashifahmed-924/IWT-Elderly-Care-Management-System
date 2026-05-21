import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import StatCard from '../components/StatCard';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { userAPI, elderAPI } from '../services/api';

const Dashboard = () => {
  const [stats, setStats] = useState(null);
  const [users, setUsers] = useState([]);
  const [elders, setElders] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [assignForm, setAssignForm] = useState({ caregiverId: '', elderId: '' });
  const [success, setSuccess] = useState('');

  const fetchData = async () => {
    try {
      const [statsRes, usersRes, eldersRes] = await Promise.all([
        userAPI.getStats(),
        userAPI.getUsers(),
        elderAPI.getAll(),
      ]);
      setStats(statsRes.data);
      setUsers(usersRes.data);
      setElders(eldersRes.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load dashboard');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleAssign = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    try {
      await userAPI.assignCaregiver(assignForm);
      setSuccess('Caregiver assigned successfully');
      setAssignForm({ caregiverId: '', elderId: '' });
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Assignment failed');
    }
  };

  const handleToggleActive = async (user) => {
    try {
      await userAPI.updateUser(user._id, { isActive: !user.isActive });
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Update failed');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this user?')) return;
    try {
      await userAPI.deleteUser(id);
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Delete failed');
    }
  };

  if (loading) return <LoadingSpinner />;

  const caregivers = users.filter((u) => u.role === 'caregiver');

  return (
    <Layout>
      <h1 className="text-2xl font-bold text-slate-800">Admin Dashboard</h1>
      <p className="mt-1 text-slate-500">Manage users, elders, and assignments</p>

      <Alert message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard title="Total Users" value={stats?.totalUsers ?? 0} icon="👥" />
        <StatCard title="Elderly Users" value={stats?.elders ?? 0} icon="👴" color="green" />
        <StatCard title="Caregivers" value={stats?.caregivers ?? 0} icon="🩺" color="amber" />
        <StatCard
          title="Appointments"
          value={stats?.appointments ?? 0}
          icon="📅"
          color="purple"
        />
      </div>

      <div className="mt-8 card">
        <h2 className="text-lg font-semibold">Assign Caregiver to Elder</h2>
        <form onSubmit={handleAssign} className="mt-4 grid gap-4 sm:grid-cols-3">
          <select
            className="input-field"
            value={assignForm.caregiverId}
            onChange={(e) =>
              setAssignForm({ ...assignForm, caregiverId: e.target.value })
            }
            required
          >
            <option value="">Select Caregiver</option>
            {caregivers.map((c) => (
              <option key={c._id} value={c._id}>
                {c.name}
              </option>
            ))}
          </select>
          <select
            className="input-field"
            value={assignForm.elderId}
            onChange={(e) => setAssignForm({ ...assignForm, elderId: e.target.value })}
            required
          >
            <option value="">Select Elder</option>
            {elders.map((e) => (
              <option key={e._id} value={e._id}>
                {e.user?.name}
              </option>
            ))}
          </select>
          <button type="submit" className="btn-primary">
            Assign
          </button>
        </form>
      </div>

      <div className="mt-8 card overflow-x-auto">
        <h2 className="mb-4 text-lg font-semibold">User Management</h2>
        <table className="w-full min-w-[600px] text-left text-sm">
          <thead>
            <tr className="border-b border-slate-200 text-slate-500">
              <th className="pb-3 pr-4">Name</th>
              <th className="pb-3 pr-4">Email</th>
              <th className="pb-3 pr-4">Role</th>
              <th className="pb-3 pr-4">Status</th>
              <th className="pb-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u._id} className="border-b border-slate-100">
                <td className="py-3 pr-4 font-medium">{u.name}</td>
                <td className="py-3 pr-4">{u.email}</td>
                <td className="py-3 pr-4 capitalize">{u.role}</td>
                <td className="py-3 pr-4">
                  <span
                    className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                      u.isActive
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                    }`}
                  >
                    {u.isActive ? 'Active' : 'Inactive'}
                  </span>
                </td>
                <td className="py-3">
                  <button
                    onClick={() => handleToggleActive(u)}
                    className="mr-2 text-primary-600 hover:underline"
                  >
                    {u.isActive ? 'Deactivate' : 'Activate'}
                  </button>
                  <button
                    onClick={() => handleDelete(u._id)}
                    className="text-red-600 hover:underline"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </Layout>
  );
};

export default Dashboard;
