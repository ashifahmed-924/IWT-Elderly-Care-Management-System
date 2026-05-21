import Elder from '../models/Elder.js';
import User from '../models/User.js';

const parseArrayField = (value) => {
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    return value.split(',').map((s) => s.trim()).filter(Boolean);
  }
  return [];
};

export const getMyProfile = async (req, res) => {
  try {
    const elder = await Elder.findOne({ user: req.user._id })
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone');

    if (!elder) {
      return res.status(404).json({ message: 'Elder profile not found' });
    }
    res.json(elder);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const updateMyProfile = async (req, res) => {
  try {
    const elder = await Elder.findOne({ user: req.user._id });
    if (!elder) {
      return res.status(404).json({ message: 'Elder profile not found' });
    }

    const fields = [
      'dateOfBirth',
      'address',
      'bloodType',
      'emergencyContact',
    ];
    fields.forEach((field) => {
      if (req.body[field] !== undefined) elder[field] = req.body[field];
    });

    if (req.body.allergies !== undefined) {
      elder.allergies = parseArrayField(req.body.allergies);
    }
    if (req.body.medications !== undefined) {
      elder.medications = parseArrayField(req.body.medications);
    }
    if (req.body.conditions !== undefined) {
      elder.conditions = parseArrayField(req.body.conditions);
    }

    await elder.save();
    const updated = await Elder.findById(elder._id)
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone');

    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getAssignedElders = async (req, res) => {
  try {
    const elders = await Elder.find({ assignedCaregiver: req.user._id })
      .populate('user', 'name email phone')
      .sort({ updatedAt: -1 });
    res.json(elders);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getElderById = async (req, res) => {
  try {
    const elder = await Elder.findById(req.params.id)
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone');

    if (!elder) {
      return res.status(404).json({ message: 'Elder not found' });
    }

    if (req.user.role === 'caregiver') {
      const isAssigned =
        elder.assignedCaregiver?.toString() === req.user._id.toString();
      if (!isAssigned) {
        return res.status(403).json({ message: 'Not assigned to this elder' });
      }
    }

    if (req.user.role === 'elderly') {
      if (elder.user._id.toString() !== req.user._id.toString()) {
        return res.status(403).json({ message: 'Access denied' });
      }
    }

    res.json(elder);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getAllElders = async (req, res) => {
  try {
    const elders = await Elder.find()
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone')
      .sort({ createdAt: -1 });
    res.json(elders);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const updateElderHealth = async (req, res) => {
  try {
    const elder = await Elder.findById(req.params.id);
    if (!elder) {
      return res.status(404).json({ message: 'Elder not found' });
    }

    if (
      req.user.role === 'caregiver' &&
      elder.assignedCaregiver?.toString() !== req.user._id.toString()
    ) {
      return res.status(403).json({ message: 'Not assigned to this elder' });
    }

    if (req.body.healthStatus) elder.healthStatus = req.body.healthStatus;
    if (req.body.notes !== undefined) elder.notes = req.body.notes;

    await elder.save();
    const updated = await Elder.findById(elder._id)
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone');

    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
