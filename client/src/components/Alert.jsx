const Alert = ({ type = 'error', message, onClose }) => {
  const styles = {
    error: 'bg-red-50 text-red-800 border-red-200',
    success: 'bg-green-50 text-green-800 border-green-200',
    info: 'bg-blue-50 text-blue-800 border-blue-200',
  };

  if (!message) return null;

  return (
    <div
      className={`mb-4 flex items-center justify-between rounded-lg border px-4 py-3 text-sm ${styles[type]}`}
    >
      <span>{message}</span>
      {onClose && (
        <button onClick={onClose} className="ml-4 font-bold opacity-60 hover:opacity-100">
          ×
        </button>
      )}
    </div>
  );
};

export default Alert;
