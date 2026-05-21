import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Layout from '../components/Layout';
import hero1 from '../assets/hero1.jpg';
import card1 from '../assets/card1.jpg';
import card2 from '../assets/card2.jpg';
import card3 from '../assets/card3.jpg';
import card4 from '../assets/card4.jpg';
import card5 from '../assets/card5.jpg';

const Home = () => {
  const { user } = useAuth();

  const features = [
    {
      image: card1,
      title: 'Elder Profile Management',
      desc: 'Elderly users can view and update profiles, health details, and appointments.',
    },
    {
      image: card2,
      title: 'Caregiver Support',
      desc: 'Caregivers track assigned elders, update health status, and add care notes.',
    },
    {
      image: card3,
      title: 'Appointment Scheduling',
      desc: 'Admins manage appointments between elders and caregivers seamlessly.',
    },
    {
      image: card4,
      title: 'Secure Access',
      desc: 'JWT authentication with role-based access for Admin, Caregiver, and Elderly.',
    },
  ];

  return (
    <Layout>
      <section className="relative overflow-hidden rounded-2xl px-8 py-16 text-white shadow-lg">
        <img
          src={hero1}
          alt=""
          aria-hidden
          className="absolute inset-0 h-full w-full scale-100 object-cover blur-[2px]"
        />
        <div className="absolute inset-0 bg-slate-900/50" />
        <div className="relative z-10 max-w-2xl">
          <h1 className="text-4xl font-bold tracking-tight drop-shadow-sm sm:text-5xl">
            Compassionate Elder Care Management
          </h1>
          <p className="mt-4 text-lg text-white/90 drop-shadow-sm">
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
      </section>

      <section className="mt-16">
        <h2 className="text-center text-2xl font-bold text-slate-800">Platform Features</h2>
        <p className="mt-2 text-center text-slate-500">
          Everything you need for coordinated elder care
        </p>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {features.map((f) => (
            <div
              key={f.title}
              className="relative min-h-[220px] overflow-hidden rounded-xl border border-slate-200 p-6 shadow-sm transition hover:shadow-md"
            >
              <img
                src={f.image}
                alt=""
                aria-hidden
                className="absolute inset-0 h-full w-full scale-105 object-cover blur-[2px]"
              />
              <div className="absolute inset-0 bg-slate-900/50" />
              <div className="relative z-10 flex h-full flex-col justify-center text-center">
                <h3 className="font-semibold text-white drop-shadow-sm">{f.title}</h3>
                <p className="mt-2 text-sm text-white/90 drop-shadow-sm">{f.desc}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

      <section className="relative mt-16 overflow-hidden rounded-2xl border border-slate-200 p-8 text-center shadow-lg">
        <img
          src={card5}
          alt=""
          aria-hidden
          className="absolute inset-0 h-full w-full scale-105 object-cover blur-[2px]"
        />
        <div className="absolute inset-0 bg-slate-900/50" />
        <div className="relative z-10">
          <h2 className="text-xl font-bold text-white drop-shadow-sm">Three Role-Based Portals</h2>
          <div className="mt-6 grid gap-4 sm:grid-cols-3">
            {['Admin', 'Caregiver', 'Elderly User'].map((role) => (
              <div
                key={role}
                className="rounded-xl border border-white/30 bg-white/20 px-4 py-6 font-medium text-white backdrop-blur-sm"
              >
                {role}
              </div>
            ))}
          </div>
        </div>
      </section>
    </Layout>
  );
};

export default Home;
