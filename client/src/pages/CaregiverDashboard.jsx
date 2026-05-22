import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Alert from '../components/Alert';
import LoadingSpinner from '../components/LoadingSpinner';
import { elderAPI, healthAPI } from '../services/api';

const CaregiverDashboard = () => {
  const [elders, setElders] = useState([]);
  const [selectedElder, setSelectedElder] = useState(null);
  const [healthRecords, setHealthRecords] = useState([]);
  const [healthForm, setHealthForm] = useState({
    healthStatus: 'stable',
    notes: '',
  });
  const [recordForm, setRecordForm] = useState({
    bloodPressure: '',
    heartRate: '',
    temperature: '',
    weight: '',
    bloodSugar: '',
    oxygenLevel: '',
    notes: '',
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(true);

  const fetchElders = async () => {
    try {
      const { data } = await elderAPI.getAssigned();
      setElders(data);
      if (data.length > 0 && !selectedElder) {
        selectElder(data[0]);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load assigned elders');
    } finally {
      setLoading(false);
    }
  };

  const selectElder = async (elder) => {
    setSelectedElder(elder);
    setHealthForm({
      healthStatus: elder.healthStatus || 'stable',
      notes: elder.notes || '',
    });
    try {
      const { data } = await healthAPI.getRecords(elder._id);
      setHealthRecords(data);
    } catch {
      setHealthRecords([]);
    }
  };

  useEffect(() => {
    fetchElders();
  }, []);

  const handleHealthUpdate = async (e) => {
    e.preventDefault();
    if (!selectedElder) return;
    setError('');
    setSuccess('');
    try {
      const { data } = await elderAPI.updateHealth(selectedElder._id, healthForm);
      setSelectedElder(data);
      setSuccess('Health status updated');
      fetchElders();
    } catch (err) {
      setError(err.response?.data?.message || 'Update failed');
    }
  };

  const handleRecordSubmit = async (e) => {
    e.preventDefault();
    if (!selectedElder) return;
    setError('');
    setSuccess('');
    try {
      const payload = {
        ...recordForm,
        heartRate: recordForm.heartRate ? Number(recordForm.heartRate) : undefined,
        temperature: recordForm.temperature
          ? Number(recordForm.temperature)
          : undefined,
        weight: recordForm.weight ? Number(recordForm.weight) : undefined,
        bloodSugar: recordForm.bloodSugar
          ? Number(recordForm.bloodSugar)
          : undefined,
        oxygenLevel: recordForm.oxygenLevel
          ? Number(recordForm.oxygenLevel)
          : undefined,
      };
      await healthAPI.createRecord(selectedElder._id, payload);
      setSuccess('Health record added');
      setRecordForm({
        bloodPressure: '',
        heartRate: '',
        temperature: '',
        weight: '',
        bloodSugar: '',
        oxygenLevel: '',
        notes: '',
      });
      const { data } = await healthAPI.getRecords(selectedElder._id);
      setHealthRecords(data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to add record');
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <Layout>
      <h1 className="text-2xl font-bold text-slate-800">Caregiver Dashboard</h1>
      <p className="mt-1 text-slate-500">Manage assigned elders and health records</p>

      <Alert message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      {elders.length === 0 ? (
        <div className="caregiver-panel mt-8 text-center text-slate-500">
          No elders assigned to you yet. Contact an admin for assignments.
        </div>
      ) : (
        <div className="mt-8 grid gap-6 lg:grid-cols-4">
          <div className="caregiver-panel lg:col-span-1">
            <h2 className="mb-4 font-semibold">Assigned Elders</h2>
            <ul className="space-y-2">
              {elders.map((elder) => (
                <li key={elder._id}>
                  <button
                    onClick={() => selectElder(elder)}
                    className={`w-full rounded-lg px-3 py-2 text-left text-sm transition ${
                      selectedElder?._id === elder._id
                        ? 'bg-primary-600 text-white'
                        : 'bg-slate-50 hover:bg-slate-100'
                    }`}
                  >
                    <p className="font-medium">{elder.user?.name}</p>
                    <p
                      className={`text-xs capitalize ${
                        selectedElder?._id === elder._id
                          ? 'text-primary-100'
                          : 'text-slate-500'
                      }`}
                    >
                      {elder.healthStatus}
                    </p>
                  </button>
                </li>
              ))}
            </ul>
          </div>

          {selectedElder && (
            <div className="space-y-6 lg:col-span-3">
              <div className="caregiver-panel">
                <h2 className="text-lg font-semibold">{selectedElder.user?.name}</h2>
                <p className="text-sm text-slate-500">{selectedElder.user?.email}</p>
                <div className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                  <p>
                    <span className="text-slate-500">Blood Type:</span>{' '}
                    {selectedElder.bloodType || '—'}
                  </p>
                  <p>
                    <span className="text-slate-500">Allergies:</span>{' '}
                    {(selectedElder.allergies || []).join(', ') || '—'}
                  </p>
                  <p>
                    <span className="text-slate-500">Medications:</span>{' '}
                    {(selectedElder.medications || []).join(', ') || '—'}
                  </p>
                  <p>
                    <span className="text-slate-500">Conditions:</span>{' '}
                    {(selectedElder.conditions || []).join(', ') || '—'}
                  </p>
                </div>
              </div>

              <form onSubmit={handleHealthUpdate} className="caregiver-panel space-y-4">
                <h2 className="font-semibold">Update Health Status & Notes</h2>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label className="label">Health Status</label>
                    <select
                      className="input-field"
                      value={healthForm.healthStatus}
                      onChange={(e) =>
                        setHealthForm({ ...healthForm, healthStatus: e.target.value })
                      }
                    >
                      <option value="stable">Stable</option>
                      <option value="monitoring">Monitoring</option>
                      <option value="critical">Critical</option>
                      <option value="recovering">Recovering</option>
                    </select>
                  </div>
                  <div>
                    <label className="label">Care Notes</label>
                    <input
                      className="input-field"
                      value={healthForm.notes}
                      onChange={(e) =>
                        setHealthForm({ ...healthForm, notes: e.target.value })
                      }
                      placeholder="General care notes..."
                    />
                  </div>
                </div>
                <button type="submit" className="btn-primary">
                  Update Status
                </button>
              </form>

              <form onSubmit={handleRecordSubmit} className="caregiver-panel space-y-4">
                <h2 className="font-semibold">Add Health Record</h2>
                <div className="grid gap-4 sm:grid-cols-3">
                  {[
                    ['bloodPressure', 'Blood Pressure', '120/80'],
                    ['heartRate', 'Heart Rate', '72'],
                    ['temperature', 'Temperature (°F)', '98.6'],
                    ['weight', 'Weight (lbs)', '150'],
                    ['bloodSugar', 'Blood Sugar', '100'],
                    ['oxygenLevel', 'Oxygen %', '98'],
                  ].map(([name, label, placeholder]) => (
                    <div key={name}>
                      <label className="label">{label}</label>
                      <input
                        name={name}
                        className="input-field"
                        value={recordForm[name]}
                        onChange={(e) =>
                          setRecordForm({ ...recordForm, [name]: e.target.value })
                        }
                        placeholder={placeholder}
                      />
                    </div>
                  ))}
                </div>
                <div>
                  <label className="label">Notes</label>
                  <textarea
                    className="input-field"
                    rows={2}
                    value={recordForm.notes}
                    onChange={(e) =>
                      setRecordForm({ ...recordForm, notes: e.target.value })
                    }
                  />
                </div>
                <button type="submit" className="btn-primary">
                  Add Record
                </button>
              </form>

              <div className="caregiver-panel">
                <h2 className="mb-4 font-semibold">Health Record History</h2>
                {healthRecords.length === 0 ? (
                  <p className="text-sm text-slate-500">No records yet.</p>
                ) : (
                  <ul className="space-y-3">
                    {healthRecords.map((r) => (
                      <li
                        key={r._id}
                        className="caregiver-panel-item"
                      >
                        <p className="font-medium">
                          {new Date(r.recordDate).toLocaleString()}
                        </p>
                        <p className="mt-1 text-slate-600">
                          BP: {r.bloodPressure || '—'} | HR: {r.heartRate || '—'} |
                          Temp: {r.temperature || '—'}°F | O₂: {r.oxygenLevel || '—'}%
                        </p>
                        {r.notes && <p className="mt-1 text-slate-500">{r.notes}</p>}
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            </div>
          )}
        </div>
      )}
    </Layout>
  );
};

export default CaregiverDashboard;
