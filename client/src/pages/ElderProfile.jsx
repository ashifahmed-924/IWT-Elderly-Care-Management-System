import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { elderAPI, appointmentAPI, healthAPI } from '../services/api';

const ElderProfile = () => {
  const [profile, setProfile] = useState(null);
  const [appointments, setAppointments] = useState([]);
  const [healthRecords, setHealthRecords] = useState([]);
  const [form, setForm] = useState({});
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const fetchData = async () => {
    try {
      const profileRes = await elderAPI.getMyProfile();
      const elder = profileRes.data;
      setProfile(elder);
      setForm({
        dateOfBirth: elder.dateOfBirth
          ? new Date(elder.dateOfBirth).toISOString().split('T')[0]
          : '',
        address: elder.address || '',
        bloodType: elder.bloodType || '',
        allergies: (elder.allergies || []).join(', '),
        medications: (elder.medications || []).join(', '),
        conditions: (elder.conditions || []).join(', '),
        emergencyContact: {
          name: elder.emergencyContact?.name || '',
          phone: elder.emergencyContact?.phone || '',
          relationship: elder.emergencyContact?.relationship || '',
        },
      });

      const [apptRes, healthRes] = await Promise.all([
        appointmentAPI.getAll(),
        healthAPI.getRecords(elder._id),
      ]);
      setAppointments(apptRes.data);
      setHealthRecords(healthRes.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load profile');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    if (name.startsWith('ec_')) {
      const field = name.replace('ec_', '');
      setForm({
        ...form,
        emergencyContact: { ...form.emergencyContact, [field]: value },
      });
    } else {
      setForm({ ...form, [name]: value });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');
    setSuccess('');
    try {
      const { data } = await elderAPI.updateMyProfile(form);
      setProfile(data);
      setSuccess('Profile updated successfully');
    } catch (err) {
      setError(err.response?.data?.message || 'Update failed');
    } finally {
      setSaving(false);
    }
  };

  const statusColors = {
    stable: 'bg-green-100 text-green-700',
    monitoring: 'bg-amber-100 text-amber-700',
    critical: 'bg-red-100 text-red-700',
    recovering: 'bg-blue-100 text-blue-700',
  };

  if (loading) return <LoadingSpinner />;

  return (
    <Layout>
      <h1 className="text-2xl font-bold text-slate-800">My Profile</h1>
      <p className="mt-1 text-slate-500">View and update your health information</p>

      <Alert message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      {profile && (
        <div className="mt-6 flex flex-wrap items-center gap-3">
          <span
            className={`rounded-full px-3 py-1 text-sm font-medium capitalize ${statusColors[profile.healthStatus]}`}
          >
            Health: {profile.healthStatus}
          </span>
          {profile.assignedCaregiver && (
            <span className="text-sm text-slate-600">
              Caregiver: <strong>{profile.assignedCaregiver.name}</strong>
            </span>
          )}
        </div>
      )}

      <form onSubmit={handleSubmit} className="mt-8 grid gap-6 lg:grid-cols-2">
        <div className="card space-y-4">
          <h2 className="text-lg font-semibold">Personal Details</h2>
          <div>
            <label className="label">Date of Birth</label>
            <input
              type="date"
              name="dateOfBirth"
              className="input-field"
              value={form.dateOfBirth}
              onChange={handleChange}
            />
          </div>
          <div>
            <label className="label">Address</label>
            <input
              name="address"
              className="input-field"
              value={form.address}
              onChange={handleChange}
            />
          </div>
          <div>
            <label className="label">Blood Type</label>
            <input
              name="bloodType"
              className="input-field"
              value={form.bloodType}
              onChange={handleChange}
              placeholder="e.g. O+"
            />
          </div>
          <button type="submit" className="btn-primary" disabled={saving}>
            {saving ? 'Saving...' : 'Save Profile'}
          </button>
        </div>

        <div className="card space-y-4">
          <h2 className="text-lg font-semibold">Health Details</h2>
          <div>
            <label className="label">Allergies (comma-separated)</label>
            <input
              name="allergies"
              className="input-field"
              value={form.allergies}
              onChange={handleChange}
            />
          </div>
          <div>
            <label className="label">Medications</label>
            <input
              name="medications"
              className="input-field"
              value={form.medications}
              onChange={handleChange}
            />
          </div>
          <div>
            <label className="label">Medical Conditions</label>
            <input
              name="conditions"
              className="input-field"
              value={form.conditions}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="card space-y-4 lg:col-span-2">
          <h2 className="text-lg font-semibold">Emergency Contact</h2>
          <div className="grid gap-4 sm:grid-cols-3">
            <div>
              <label className="label">Name</label>
              <input
                name="ec_name"
                className="input-field"
                value={form.emergencyContact?.name}
                onChange={handleChange}
              />
            </div>
            <div>
              <label className="label">Phone</label>
              <input
                name="ec_phone"
                className="input-field"
                value={form.emergencyContact?.phone}
                onChange={handleChange}
              />
            </div>
            <div>
              <label className="label">Relationship</label>
              <input
                name="ec_relationship"
                className="input-field"
                value={form.emergencyContact?.relationship}
                onChange={handleChange}
              />
            </div>
          </div>
        </div>
      </form>

      <div className="mt-8 grid gap-6 lg:grid-cols-2">
        <div className="card">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold">My Appointments</h2>
            <Link to="/appointments" className="text-sm text-primary-600 hover:underline">
              View all
            </Link>
          </div>
          {appointments.length === 0 ? (
            <p className="text-sm text-slate-500">No appointments scheduled.</p>
          ) : (
            <ul className="space-y-3">
              {appointments.slice(0, 5).map((a) => (
                <li
                  key={a._id}
                  className="rounded-lg border border-slate-100 p-3 text-sm"
                >
                  <p className="font-medium">{a.title}</p>
                  <p className="text-slate-500">
                    {new Date(a.date).toLocaleDateString()} at {a.time}
                  </p>
                  <span className="mt-1 inline-block rounded bg-primary-50 px-2 py-0.5 text-xs capitalize text-primary-700">
                    {a.status}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="card">
          <h2 className="mb-4 text-lg font-semibold">Recent Health Records</h2>
          {healthRecords.length === 0 ? (
            <p className="text-sm text-slate-500">No health records yet.</p>
          ) : (
            <ul className="space-y-3">
              {healthRecords.slice(0, 5).map((r) => (
                <li
                  key={r._id}
                  className="rounded-lg border border-slate-100 p-3 text-sm"
                >
                  <p className="text-slate-500">
                    {new Date(r.recordDate).toLocaleDateString()} — by{' '}
                    {r.recordedBy?.name}
                  </p>
                  <p className="mt-1">
                    BP: {r.bloodPressure || '—'} | HR: {r.heartRate || '—'} | Temp:{' '}
                    {r.temperature || '—'}°F
                  </p>
                  {r.notes && <p className="mt-1 text-slate-600">{r.notes}</p>}
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </Layout>
  );
};

export default ElderProfile;
