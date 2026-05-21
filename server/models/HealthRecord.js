import mongoose from 'mongoose';

const healthRecordSchema = new mongoose.Schema(
  {
    elder: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Elder',
      required: true,
    },
    bloodPressure: { type: String, trim: true },
    heartRate: { type: Number },
    temperature: { type: Number },
    weight: { type: Number },
    bloodSugar: { type: Number },
    oxygenLevel: { type: Number },
    notes: { type: String, trim: true },
    recordedBy: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true,
    },
    recordDate: { type: Date, default: Date.now },
  },
  { timestamps: true }
);

const HealthRecord = mongoose.model('HealthRecord', healthRecordSchema);
export default HealthRecord;
