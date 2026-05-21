import mongoose from 'mongoose';

const elderSchema = new mongoose.Schema(
  {
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true,
      unique: true,
    },
    dateOfBirth: { type: Date },
    address: { type: String, trim: true },
    emergencyContact: {
      name: { type: String, trim: true },
      phone: { type: String, trim: true },
      relationship: { type: String, trim: true },
    },
    bloodType: { type: String, trim: true },
    allergies: [{ type: String, trim: true }],
    medications: [{ type: String, trim: true }],
    conditions: [{ type: String, trim: true }],
    healthStatus: {
      type: String,
      enum: ['stable', 'monitoring', 'critical', 'recovering'],
      default: 'stable',
    },
    assignedCaregiver: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
    },
    notes: { type: String, trim: true },
  },
  { timestamps: true }
);

const Elder = mongoose.model('Elder', elderSchema);
export default Elder;
