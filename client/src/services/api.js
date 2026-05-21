import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:5000/api',
  headers: { 'Content-Type': 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export const authAPI = {
  register: (data) => api.post('/auth/register', data),
  login: (data) => api.post('/auth/login', data),
  getMe: () => api.get('/auth/me'),
};

export const userAPI = {
  getUsers: (params) => api.get('/users', { params }),
  getUser: (id) => api.get(`/users/${id}`),
  updateUser: (id, data) => api.put(`/users/${id}`, data),
  deleteUser: (id) => api.delete(`/users/${id}`),
  assignCaregiver: (data) => api.post('/users/assign-caregiver', data),
  getStats: () => api.get('/users/stats'),
};

export const elderAPI = {
  getMyProfile: () => api.get('/elders/profile/me'),
  updateMyProfile: (data) => api.put('/elders/profile/me', data),
  getAssigned: () => api.get('/elders/assigned'),
  getAll: () => api.get('/elders'),
  getById: (id) => api.get(`/elders/${id}`),
  updateHealth: (id, data) => api.put(`/elders/${id}/health`, data),
};

export const appointmentAPI = {
  getAll: () => api.get('/appointments'),
  getById: (id) => api.get(`/appointments/${id}`),
  create: (data) => api.post('/appointments', data),
  update: (id, data) => api.put(`/appointments/${id}`, data),
  delete: (id) => api.delete(`/appointments/${id}`),
};

export const healthAPI = {
  getRecords: (elderId) => api.get(`/health-records/${elderId}`),
  createRecord: (elderId, data) => api.post(`/health-records/${elderId}`, data),
  updateRecord: (id, data) => api.put(`/health-records/record/${id}`, data),
};

export default api;
