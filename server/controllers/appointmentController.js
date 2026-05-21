import Appointment from '../models/Appointment.js';
import Elder from '../models/Elder.js';

export const getAppointments = async (req, res) => {
  try {
    let filter = {};

    if (req.user.role === 'elderly') {
      const elder = await Elder.findOne({ user: req.user._id });
      if (!elder) return res.json([]);
      filter.elder = elder._id;
    } else if (req.user.role === 'caregiver') {
      filter.caregiver = req.user._id;
    }

    const appointments = await Appointment.find(filter)
      .populate({
        path: 'elder',
        populate: { path: 'user', select: 'name email' },
      })
      .populate('caregiver', 'name email')
      .sort({ date: 1 });

    res.json(appointments);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getAppointmentById = async (req, res) => {
  try {
    const appointment = await Appointment.findById(req.params.id)
      .populate({
        path: 'elder',
        populate: { path: 'user', select: 'name email' },
      })
      .populate('caregiver', 'name email');

    if (!appointment) {
      return res.status(404).json({ message: 'Appointment not found' });
    }
    res.json(appointment);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const createAppointment = async (req, res) => {
  try {
    const { elder, caregiver, title, description, date, time, location, status } =
      req.body;

    const appointment = await Appointment.create({
      elder,
      caregiver,
      title,
      description,
      date,
      time,
      location,
      status,
      createdBy: req.user._id,
    });

    const populated = await Appointment.findById(appointment._id)
      .populate({
        path: 'elder',
        populate: { path: 'user', select: 'name email' },
      })
      .populate('caregiver', 'name email');

    res.status(201).json(populated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const updateAppointment = async (req, res) => {
  try {
    const appointment = await Appointment.findById(req.params.id);
    if (!appointment) {
      return res.status(404).json({ message: 'Appointment not found' });
    }

    const fields = [
      'elder',
      'caregiver',
      'title',
      'description',
      'date',
      'time',
      'location',
      'status',
    ];
    fields.forEach((field) => {
      if (req.body[field] !== undefined) appointment[field] = req.body[field];
    });

    await appointment.save();
    const updated = await Appointment.findById(appointment._id)
      .populate({
        path: 'elder',
        populate: { path: 'user', select: 'name email' },
      })
      .populate('caregiver', 'name email');

    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const deleteAppointment = async (req, res) => {
  try {
    const appointment = await Appointment.findById(req.params.id);
    if (!appointment) {
      return res.status(404).json({ message: 'Appointment not found' });
    }
    await appointment.deleteOne();
    res.json({ message: 'Appointment removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
