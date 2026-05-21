# Elder Care Management System

A full-stack MERN application for managing elderly care with role-based access for **Admin**, **Caregiver**, and **Elderly User** roles.

## Features

- JWT authentication (register / login)
- **Elderly users**: view/update profile, health details, appointments
- **Caregivers**: view assigned elders, update health status, add notes & health records
- **Admin**: user management, caregiver assignment, appointment CRUD, dashboard stats

## Tech Stack

| Layer    | Technology                          |
|----------|-------------------------------------|
| Frontend | React, Vite, Tailwind CSS, Axios    |
| Backend  | Node.js, Express.js, Mongoose       |
| Database | MongoDB                             |
| Auth     | JWT (jsonwebtoken + bcryptjs)        |

## Project Structure

```
iwt/
├── client/                 # React frontend
│   └── src/
│       ├── components/
│       ├── context/
│       ├── pages/
│       └── services/
└── server/                 # Express API (MVC)
    ├── config/
    ├── controllers/
    ├── middleware/
    ├── models/
    └── routes/
```

## Prerequisites

- [Node.js](https://nodejs.org/) (v18+)
- MongoDB Atlas cluster (configured in `server/.env`) or a local MongoDB instance

## Setup

### 1. Backend

```bash
cd server
npm install
cp .env.example .env   # set your Atlas URI, username, password, and JWT_SECRET
npm run dev
```

Server runs at **http://localhost:5000**

**Seed sample data (optional):**

```bash
npm run seed
```

This adds demo users, elders, appointments, and health records. All demo accounts use password `123456`.

### 2. Frontend

```bash
cd client
npm install
npm run dev
```

App runs at **http://localhost:5173**

## API Endpoints

| Method | Endpoint | Access |
|--------|----------|--------|
| POST | `/api/auth/register` | Public |
| POST | `/api/auth/login` | Public |
| GET | `/api/auth/me` | Authenticated |
| GET | `/api/users` | Admin |
| POST | `/api/users/assign-caregiver` | Admin |
| GET | `/api/elders/profile/me` | Elderly |
| GET | `/api/elders/assigned` | Caregiver |
| GET/POST/PUT/DELETE | `/api/appointments` | Role-based |
| GET/POST | `/api/health-records/:elderId` | Role-based |

## Usage Flow

1. **Register** users with the desired role (Admin, Caregiver, or Elderly User).
2. **Admin** assigns caregivers to elders from the dashboard.
3. **Admin** creates appointments linking elders and caregivers.
4. **Caregivers** update health status and add vitals/notes for assigned elders.
5. **Elderly users** maintain their profile and view appointments/records.

## License

MIT
