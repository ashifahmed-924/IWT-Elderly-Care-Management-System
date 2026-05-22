const StatCard = ({ title, value, icon, color = 'primary' }) => {
  const colors = {
    primary: 'bg-primary-50 text-primary-700',
    green: 'bg-green-50 text-green-700',
    amber: 'bg-amber-50 text-amber-700',
    purple: 'bg-purple-50 text-purple-700',
  };

  return (
    <div className="dashboard-panel flex items-center gap-4">
      <div
        className={`flex h-12 w-12 items-center justify-center rounded-xl text-2xl ${colors[color]}`}
      >
        {icon}
      </div>
      <div>
        <p className="text-sm text-slate-500">{title}</p>
        <p className="text-2xl font-bold text-slate-800">{value}</p>
      </div>
    </div>
  );
};

export default StatCard;
