import express from 'express';
import {
  getMyProfile,
  updateMyProfile,
  getAssignedElders,
  getElderById,
  getAllElders,
  updateElderHealth,
} from '../controllers/elderController.js';
import { protect, authorize } from '../middleware/auth.js';

const router = express.Router();

router.use(protect);

router.get('/profile/me', authorize('elderly'), getMyProfile);
router.put('/profile/me', authorize('elderly'), updateMyProfile);
router.get('/assigned', authorize('caregiver'), getAssignedElders);
router.get('/', authorize('admin'), getAllElders);
router.get('/:id', authorize('admin', 'caregiver', 'elderly'), getElderById);
router.put('/:id/health', authorize('admin', 'caregiver'), updateElderHealth);

export default router;
