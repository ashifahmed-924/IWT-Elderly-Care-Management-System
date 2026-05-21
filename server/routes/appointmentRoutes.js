import express from 'express';
import {
  getAppointments,
  getAppointmentById,
  createAppointment,
  updateAppointment,
  deleteAppointment,
} from '../controllers/appointmentController.js';
import { protect, authorize } from '../middleware/auth.js';

const router = express.Router();

router.use(protect);

router.get('/', getAppointments);
router.get('/:id', getAppointmentById);
router.post('/', authorize('admin'), createAppointment);
router.put('/:id', authorize('admin'), updateAppointment);
router.delete('/:id', authorize('admin'), deleteAppointment);

export default router;
