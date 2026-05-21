const LoadingSpinner = ({ message = 'Loading...' }) => (
  <div className="flex min-h-[40vh] flex-col items-center justify-center gap-3">
    <div className="h-10 w-10 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600" />
    <p className="text-sm text-slate-500">{message}</p>
  </div>
);

export default LoadingSpinner;
