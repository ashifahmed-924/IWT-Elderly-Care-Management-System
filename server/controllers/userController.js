import User from '../models/User.js';
import Elder from '../models/Elder.js';
import Appointment from '../models/Appointment.js';

export const getUsers = async (req, res) => {
  try {
    const { role } = req.query;
    const filter = role ? { role } : {};
    const users = await User.find(filter)
      .populate('elderProfile')
      .select('-password')
      .sort({ createdAt: -1 });
    res.json(users);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getUserById = async (req, res) => {
  try {
    const user = await User.findById(req.params.id)
      .populate('elderProfile')
      .select('-password');
    if (!user) return res.status(404).json({ message: 'User not found' });
    res.json(user);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const updateUser = async (req, res) => {
  try {
    const { name, email, phone, role, isActive } = req.body;
    const user = await User.findById(req.params.id);
    if (!user) return res.status(404).json({ message: 'User not found' });

    if (name) user.name = name;
    if (email) user.email = email;
    if (phone !== undefined) user.phone = phone;
    if (role) user.role = role;
    if (isActive !== undefined) user.isActive = isActive;

    await user.save();
    const updated = await User.findById(user._id)
      .populate('elderProfile')
      .select('-password');
    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const deleteUser = async (req, res) => {
  try {
    const user = await User.findById(req.params.id);
    if (!user) return res.status(404).json({ message: 'User not found' });

    if (user.elderProfile) {
      await Elder.findByIdAndDelete(user.elderProfile);
    }
    await user.deleteOne();
    res.json({ message: 'User removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const assignCaregiver = async (req, res) => {
  try {
    const { caregiverId, elderId } = req.body;

    const caregiver = await User.findById(caregiverId);
    const elder = await Elder.findById(elderId).populate('user');

    if (!caregiver || caregiver.role !== 'caregiver') {
      return res.status(400).json({ message: 'Invalid caregiver' });
    }
    if (!elder) {
      return res.status(404).json({ message: 'Elder not found' });
    }

    if (elder.assignedCaregiver) {
      await User.findByIdAndUpdate(elder.assignedCaregiver, {
        $pull: { assignedElders: elder._id },
      });
    }

    elder.assignedCaregiver = caregiverId;
    await elder.save();

    await User.findByIdAndUpdate(caregiverId, {
      $addToSet: { assignedElders: elder._id },
    });

    const updated = await Elder.findById(elderId)
      .populate('user', 'name email phone')
      .populate('assignedCaregiver', 'name email phone');

    res.json(updated);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

export const getDashboardStats = async (req, res) => {
  try {
    const [totalUsers, elders, caregivers, appointments] = await Promise.all([
      User.countDocuments(),
      User.countDocuments({ role: 'elderly' }),
      User.countDocuments({ role: 'caregiver' }),
      Appointment.countDocuments(),
    ]);

    res.json({ totalUsers, elders, caregivers, appointments });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
