import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { useAuth } from '../context/AuthContext';
import { appointmentAPI, elderAPI, userAPI } from '../services/api';

const emptyForm = {
  elder: '',
  caregiver: '',
  title: '',
  description: '',
  date: '',
  time: '',
  location: '',
  status: 'scheduled',
};

const Appointments = () => {
  const { user } = useAuth();
  const isAdmin = user?.role === 'admin';
  const [appointments, setAppointments] = useState([]);
  const [elders, setElders] = useState([]);
  const [caregivers, setCaregivers] = useState([]);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(true);

  const fetchAppointments = async () => {
    try {
      const { data } = await appointmentAPI.getAll();
      setAppointments(data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load appointments');
    } finally {
      setLoading(false);
    }
  };

  const fetchAdminData = async () => {
    const [eldersRes, usersRes] = await Promise.all([
      elderAPI.getAll(),
      userAPI.getUsers({ role: 'caregiver' }),
    ]);
    setElders(eldersRes.data);
    setCaregivers(usersRes.data);
  };

  useEffect(() => {
    fetchAppointments();
    if (isAdmin) fetchAdminData();
  }, [isAdmin]);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    try {
      if (editingId) {
        await appointmentAPI.update(editingId, form);
        setSuccess('Appointment updated');
      } else {
        await appointmentAPI.create(form);
        setSuccess('Appointment created');
      }
      setForm(emptyForm);
      setEditingId(null);
      setShowForm(false);
      fetchAppointments();
    } catch (err) {
      setError(err.response?.data?.message || 'Operation failed');
    }
  };

  const handleEdit = (appt) => {
    setForm({
      elder: appt.elder?._id || appt.elder,
      caregiver: appt.caregiver?._id || appt.caregiver || '',
      title: appt.title,
      description: appt.description || '',
      date: appt.date ? new Date(appt.date).toISOString().split('T')[0] : '',
      time: appt.time,
      location: appt.location || '',
      status: appt.status,
    });
    setEditingId(appt._id);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this appointment?')) return;
    try {
      await appointmentAPI.delete(id);
      setSuccess('Appointment deleted');
      fetchAppointments();
    } catch (err) {
      setError(err.response?.data?.message || 'Delete failed');
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <Layout>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Appointments</h1>
          <p className="mt-1 text-slate-500">
            {isAdmin ? 'Manage all appointments' : 'View your scheduled appointments'}
          </p>
        </div>
        {isAdmin && (
          <button
            onClick={() => {
              setShowForm(!showForm);
              setForm(emptyForm);
              setEditingId(null);
            }}
            className="btn-primary"
          >
            {showForm ? 'Cancel' : '+ New Appointment'}
          </button>
        )}
      </div>

      <Alert message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      {isAdmin && showForm && (
        <form onSubmit={handleSubmit} className="card mt-6 grid gap-4 sm:grid-cols-2">
          <h2 className="sm:col-span-2 text-lg font-semibold">
            {editingId ? 'Edit Appointment' : 'Create Appointment'}
          </h2>
          <div>
            <label className="label">Elder</label>
            <select
              name="elder"
              className="input-field"
              value={form.elder}
              onChange={handleChange}
              required
            >
              <option value="">Select elder</option>
              {elders.map((e) => (
                <option key={e._id} value={e._id}>
                  {e.user?.name}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className="label">Caregiver</label>
            <select
              name="caregiver"
              className="input-field"
              value={form.caregiver}
              onChange={handleChange}
            >
              <option value="">Select caregiver</option>
              {caregivers.map((c) => (
                <option key={c._id} value={c._id}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className="label">Title</label>
            <input
              name="title"
              className="input-field"
              value={form.title}
              onChange={handleChange}
              required
            />
          </div>
          <div>
            <label className="label">Date</label>
            <input
              type="date"
              name="date"
              className="input-field"
              value={form.date}
              onChange={handleChange}
              required
            />
          </div>
          <div>
            <label className="label">Time</label>
            <input
              name="time"
              className="input-field"
              value={form.time}
              onChange={handleChange}
              placeholder="10:00 AM"
              required
            />
          </div>
          <div>
            <label className="label">Location</label>
            <input
              name="location"
              className="input-field"
              value={form.location}
              onChange={handleChange}
            />
          </div>
          <div>
            <label className="label">Status</label>
            <select
              name="status"
              className="input-field"
              value={form.status}
              onChange={handleChange}
            >
              <option value="scheduled">Scheduled</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rescheduled">Rescheduled</option>
            </select>
          </div>
          <div className="sm:col-span-2">
            <label className="label">Description</label>
            <textarea
              name="description"
              className="input-field"
              rows={2}
              value={form.description}
              onChange={handleChange}
            />
          </div>
          <button type="submit" className="btn-primary sm:col-span-2">
            {editingId ? 'Update' : 'Create'} Appointment
          </button>
        </form>
      )}

      <div className="card mt-8 overflow-x-auto">
        <table className="w-full min-w-[700px] text-left text-sm">
          <thead>
            <tr className="border-b border-slate-200 text-slate-500">
              <th className="pb-3 pr-4">Title</th>
              <th className="pb-3 pr-4">Elder</th>
              <th className="pb-3 pr-4">Caregiver</th>
              <th className="pb-3 pr-4">Date</th>
              <th className="pb-3 pr-4">Time</th>
              <th className="pb-3 pr-4">Status</th>
              {isAdmin && <th className="pb-3">Actions</th>}
            </tr>
          </thead>
          <tbody>
            {appointments.length === 0 ? (
              <tr>
                <td colSpan={isAdmin ? 7 : 6} className="py-8 text-center text-slate-500">
                  No appointments found
                </td>
              </tr>
            ) : (
              appointments.map((a) => (
                <tr key={a._id} className="border-b border-slate-100">
                  <td className="py-3 pr-4 font-medium">{a.title}</td>
                  <td className="py-3 pr-4">{a.elder?.user?.name || '—'}</td>
                  <td className="py-3 pr-4">{a.caregiver?.name || '—'}</td>
                  <td className="py-3 pr-4">
                    {new Date(a.date).toLocaleDateString()}
                  </td>
                  <td className="py-3 pr-4">{a.time}</td>
                  <td className="py-3 pr-4 capitalize">{a.status}</td>
                  {isAdmin && (
                    <td className="py-3">
                      <button
                        onClick={() => handleEdit(a)}
                        className="mr-2 text-primary-600 hover:underline"
                      >
                        Edit
                      </button>
                      <button
                        onClick={() => handleDelete(a._id)}
                        className="text-red-600 hover:underline"
                      >
                        Delete
                      </button>
                    </td>
                  )}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </Layout>
  );
};

export default Appointments;
