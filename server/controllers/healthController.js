import HealthRecord from '../models/HealthRecord.js';
import Elder from '../models/Elder.js';

const canAccessElder = async (user, elderId) => {
  const elder = await Elder.findById(elderId);
  if (!elder) return { allowed: false, elder: null };

  if (user.role === 'admin') return { allowed: true, elder };
  if (user.role === 'elderly' && elder.user.toString() === user._id.toString()) {
    return { allowed: true, elder };
  }
  if (
    user.role === 'caregiver' &&
    elder.assignedCaregiver?.toString() === user._id.toString()
  ) {
    return { allowed: true, elder };
  }
  return { allowed: false, elder };
};

export const getHealthRecords = async (req, res) => {
  try {
    const { elderId } = req.params;
    const { allowed } = await canAccessElder(req.user, elderId);

    if (!allowed) {
      return res.status(403).json({ message: 'Access denied' });
    }

    const records = await HealthRecord.find({ elder: elderId })
      .populate('recordedBy', 'name role')
      .sort({ recordDate: -1 });

    res.json(records);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const createHealthRecord = async (req, res) => {
  try {
    const { elderId } = req.params;
    const { allowed } = await canAccessElder(req.user, elderId);

    if (!allowed) {
      return res.status(403).json({ message: 'Access denied' });
    }

    if (req.user.role === 'elderly') {
      return res
        .status(403)
        .json({ message: 'Elderly users cannot create health records' });
    }

    const record = await HealthRecord.create({
      elder: elderId,
      ...req.body,
      recordedBy: req.user._id,
    });

    const populated = await HealthRecord.findById(record._id).populate(
      'recordedBy',
      'name role'
    );

    res.status(201).json(populated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const updateHealthRecord = async (req, res) => {
  try {
    const record = await HealthRecord.findById(req.params.id);
    if (!record) {
      return res.status(404).json({ message: 'Health record not found' });
    }

    const { allowed } = await canAccessElder(req.user, record.elder);
    if (!allowed || req.user.role === 'elderly') {
      return res.status(403).json({ message: 'Access denied' });
    }

    const fields = [
      'bloodPressure',
      'heartRate',
      'temperature',
      'weight',
      'bloodSugar',
      'oxygenLevel',
      'notes',
      'recordDate',
    ];
    fields.forEach((field) => {
      if (req.body[field] !== undefined) record[field] = req.body[field];
    });

    await record.save();
    const updated = await HealthRecord.findById(record._id).populate(
      'recordedBy',
      'name role'
    );

    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
