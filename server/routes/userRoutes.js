import express from 'express';
import {
  getUsers,
  getUserById,
  updateUser,
  deleteUser,
  assignCaregiver,
  getDashboardStats,
} from '../controllers/userController.js';
import { protect, authorize } from '../middleware/auth.js';

const router = express.Router();

router.use(protect);
router.get('/stats', authorize('admin'), getDashboardStats);
router.get('/', authorize('admin'), getUsers);
router.get('/:id', authorize('admin'), getUserById);
router.put('/:id', authorize('admin'), updateUser);
router.delete('/:id', authorize('admin'), deleteUser);
router.post('/assign-caregiver', authorize('admin'), assignCaregiver);

export default router;
