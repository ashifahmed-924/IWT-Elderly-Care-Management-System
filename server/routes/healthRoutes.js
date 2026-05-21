import express from 'express';
import {
  getHealthRecords,
  createHealthRecord,
  updateHealthRecord,
} from '../controllers/healthController.js';
import { protect } from '../middleware/auth.js';

const router = express.Router();

router.use(protect);

router.get('/:elderId', getHealthRecords);
router.post('/:elderId', createHealthRecord);
router.put('/record/:id', updateHealthRecord);

export default router;
