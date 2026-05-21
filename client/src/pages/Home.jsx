import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Layout from '../components/Layout';

const Home = () => {
  const { user } = useAuth();

  const features = [
    {
      icon: '👴',
      title: 'Elder Profile Management',
      desc: 'Elderly users can view and update profiles, health details, and appointments.',
    },
    {
      icon: '🩺',
      title: 'Caregiver Support',
      desc: 'Caregivers track assigned elders, update health status, and add care notes.',
    },
    {
      icon: '📅',
      title: 'Appointment Scheduling',
      desc: 'Admins manage appointments between elders and caregivers seamlessly.',
    },
    {
      icon: '🔒',
      title: 'Secure Access',
      desc: 'JWT authentication with role-based access for Admin, Caregiver, and Elderly.',
    },
  ];

  return (
    <Layout>
      <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 px-8 py-16 text-white shadow-lg">
        <div className="relative z-10 max-w-2xl">
          <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
            Compassionate Elder Care Management
          </h1>
          <p className="mt-4 text-lg text-primary-100">
            A modern platform connecting elderly users, caregivers, and administrators
            for coordinated health monitoring and appointment management.
          </p>
          <div className="mt-8 flex flex-wrap gap-4">
            {user ? (
              <Link
                to={
                  user.role === 'admin'
                    ? '/dashboard'
                    : user.role === 'caregiver'
                      ? '/caregiver-dashboard'
                      : '/elder-profile'
                }
                className="rounded-lg bg-white px-6 py-3 font-semibold text-primary-700 shadow hover:bg-primary-50"
              >
                Go to Dashboard
              </Link>
            ) : (
              <>
                <Link
                  to="/register"
                  className="rounded-lg bg-white px-6 py-3 font-semibold text-primary-700 shadow hover:bg-primary-50"
                >
                  Get Started
                </Link>
                <Link
                  to="/login"
                  className="rounded-lg border-2 border-white px-6 py-3 font-semibold hover:bg-white/10"
                >
                  Sign In
                </Link>
              </>
            )}
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10" />
        <div className="absolute -bottom-16 right-20 h-48 w-48 rounded-full bg-white/5" />
      </section>

      <section className="mt-16">
        <h2 className="text-center text-2xl font-bold text-slate-800">Platform Features</h2>
        <p className="mt-2 text-center text-slate-500">
          Everything you need for coordinated elder care
        </p>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {features.map((f) => (
            <div key={f.title} className="card text-center transition hover:shadow-md">
              <span className="text-4xl">{f.icon}</span>
              <h3 className="mt-4 font-semibold text-slate-800">{f.title}</h3>
              <p className="mt-2 text-sm text-slate-500">{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="mt-16 rounded-2xl border border-slate-200 bg-white p-8 text-center">
        <h2 className="text-xl font-bold text-slate-800">Three Role-Based Portals</h2>
        <div className="mt-6 grid gap-4 sm:grid-cols-3">
          {['Admin', 'Caregiver', 'Elderly User'].map((role) => (
            <div
              key={role}
              className="rounded-xl bg-slate-50 px-4 py-6 font-medium text-slate-700"
            >
              {role}
            </div>
          ))}
        </div>
      </section>
    </Layout>
  );
};

export default Home;
