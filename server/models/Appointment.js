import mongoose from 'mongoose';

const appointmentSchema = new mongoose.Schema(
  {
    elder: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Elder',
      required: true,
    },
    caregiver: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
    },
    title: { type: String, required: true, trim: true },
    description: { type: String, trim: true },
    date: { type: Date, required: true },
    time: { type: String, required: true },
    location: { type: String, trim: true },
    status: {
      type: String,
      enum: ['scheduled', 'completed', 'cancelled', 'rescheduled'],
      default: 'scheduled',
    },
    createdBy: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
    },
  },
  { timestamps: true }
);

const Appointment = mongoose.model('Appointment', appointmentSchema);
export default Appointment;
