import dotenv from 'dotenv';
import mongoose from 'mongoose';
import connectDB from '../config/db.js';
import User from '../models/User.js';
import Elder from '../models/Elder.js';
import Appointment from '../models/Appointment.js';
import HealthRecord from '../models/HealthRecord.js';

dotenv.config();

const PASSWORD = '123456';

const seed = async () => {
  try {
    await connectDB();

    console.log('Clearing existing data...');
    await Promise.all([
      User.deleteMany({}),
      Elder.deleteMany({}),
      Appointment.deleteMany({}),
      HealthRecord.deleteMany({}),
    ]);

    console.log('Creating users...');

    const admin = await User.create({
      name: 'Sarah Admin',
      email: 'admin@eldercare.com',
      password: PASSWORD,
      role: 'admin',
      phone: '+1-555-0100',
    });

    const caregiver1 = await User.create({
      name: 'James Wilson',
      email: 'james.care@eldercare.com',
      password: PASSWORD,
      role: 'caregiver',
      phone: '+1-555-0201',
    });

    const caregiver2 = await User.create({
      name: 'Maria Garcia',
      email: 'maria.care@eldercare.com',
      password: PASSWORD,
      role: 'caregiver',
      phone: '+1-555-0202',
    });

    const elderlyUsers = await User.create([
      {
        name: 'Robert Thompson',
        email: 'robert@eldercare.com',
        password: PASSWORD,
        role: 'elderly',
        phone: '+1-555-0301',
      },
      {
        name: 'Eleanor Davis',
        email: 'eleanor@eldercare.com',
        password: PASSWORD,
        role: 'elderly',
        phone: '+1-555-0302',
      },
      {
        name: 'William Chen',
        email: 'william@eldercare.com',
        password: PASSWORD,
        role: 'elderly',
        phone: '+1-555-0303',
      },
      {
        name: 'Margaret Lee',
        email: 'margaret@eldercare.com',
        password: PASSWORD,
        role: 'elderly',
        phone: '+1-555-0304',
      },
    ]);

    console.log('Creating elder profiles...');

    const elders = await Elder.create([
      {
        user: elderlyUsers[0]._id,
        dateOfBirth: new Date('1945-03-12'),
        address: '42 Oak Street, Springfield',
        bloodType: 'O+',
        allergies: ['Penicillin', 'Peanuts'],
        medications: ['Lisinopril 10mg', 'Metformin 500mg'],
        conditions: ['Hypertension', 'Type 2 Diabetes'],
        healthStatus: 'stable',
        assignedCaregiver: caregiver1._id,
        emergencyContact: {
          name: 'John Thompson',
          phone: '+1-555-0399',
          relationship: 'Son',
        },
        notes: 'Prefers morning walks. Needs help with medication reminders.',
      },
      {
        user: elderlyUsers[1]._id,
        dateOfBirth: new Date('1940-07-22'),
        address: '18 Maple Avenue, Riverside',
        bloodType: 'A-',
        allergies: ['Sulfa drugs'],
        medications: ['Amlodipine 5mg', 'Vitamin D'],
        conditions: ['Arthritis', 'Osteoporosis'],
        healthStatus: 'monitoring',
        assignedCaregiver: caregiver1._id,
        emergencyContact: {
          name: 'Lisa Davis',
          phone: '+1-555-0398',
          relationship: 'Daughter',
        },
        notes: 'Mobility assistance required. Weekly physiotherapy.',
      },
      {
        user: elderlyUsers[2]._id,
        dateOfBirth: new Date('1938-11-05'),
        address: '7 Pine Road, Lakewood',
        bloodType: 'B+',
        allergies: [],
        medications: ['Atorvastatin 20mg', 'Aspirin 81mg'],
        conditions: ['High Cholesterol'],
        healthStatus: 'recovering',
        assignedCaregiver: caregiver2._id,
        emergencyContact: {
          name: 'David Chen',
          phone: '+1-555-0397',
          relationship: 'Son',
        },
        notes: 'Recovering from hip surgery. Limited mobility for 4 weeks.',
      },
      {
        user: elderlyUsers[3]._id,
        dateOfBirth: new Date('1943-01-18'),
        address: '99 Cedar Lane, Hilltown',
        bloodType: 'AB+',
        allergies: ['Latex'],
        medications: ['Levothyroxine 50mcg'],
        conditions: ['Hypothyroidism'],
        healthStatus: 'stable',
        assignedCaregiver: caregiver2._id,
        emergencyContact: {
          name: 'Amy Lee',
          phone: '+1-555-0396',
          relationship: 'Daughter',
        },
        notes: 'Independent with daily activities. Regular thyroid checkups.',
      },
    ]);

    for (let i = 0; i < elderlyUsers.length; i++) {
      elderlyUsers[i].elderProfile = elders[i]._id;
      await elderlyUsers[i].save();
    }

    caregiver1.assignedElders = [elders[0]._id, elders[1]._id];
    caregiver2.assignedElders = [elders[2]._id, elders[3]._id];
    await caregiver1.save();
    await caregiver2.save();

    console.log('Creating appointments...');

    const now = new Date();
    const nextWeek = new Date(now);
    nextWeek.setDate(nextWeek.getDate() + 7);
    const twoWeeks = new Date(now);
    twoWeeks.setDate(twoWeeks.getDate() + 14);

    await Appointment.create([
      {
        elder: elders[0]._id,
        caregiver: caregiver1._id,
        title: 'Routine Health Checkup',
        description: 'Monthly vitals and medication review',
        date: nextWeek,
        time: '10:00 AM',
        location: 'Springfield Community Clinic',
        status: 'scheduled',
        createdBy: admin._id,
      },
      {
        elder: elders[1]._id,
        caregiver: caregiver1._id,
        title: 'Physiotherapy Session',
        description: 'Knee mobility exercises',
        date: nextWeek,
        time: '2:30 PM',
        location: 'Riverside Rehab Center',
        status: 'scheduled',
        createdBy: admin._id,
      },
      {
        elder: elders[2]._id,
        caregiver: caregiver2._id,
        title: 'Post-Surgery Follow-up',
        description: 'Hip recovery assessment with doctor',
        date: twoWeeks,
        time: '11:00 AM',
        location: 'Lakewood Medical Center',
        status: 'scheduled',
        createdBy: admin._id,
      },
      {
        elder: elders[3]._id,
        caregiver: caregiver2._id,
        title: 'Thyroid Lab Review',
        description: 'Blood work results discussion',
        date: twoWeeks,
        time: '9:30 AM',
        location: 'Hilltown Health Hub',
        status: 'scheduled',
        createdBy: admin._id,
      },
    ]);

    console.log('Creating health records...');

    await HealthRecord.create([
      {
        elder: elders[0]._id,
        bloodPressure: '128/82',
        heartRate: 72,
        temperature: 98.4,
        weight: 165,
        bloodSugar: 110,
        oxygenLevel: 97,
        notes: 'Vitals within normal range. Patient in good spirits.',
        recordedBy: caregiver1._id,
        recordDate: new Date(now.getTime() - 2 * 24 * 60 * 60 * 1000),
      },
      {
        elder: elders[1]._id,
        bloodPressure: '135/88',
        heartRate: 78,
        temperature: 98.1,
        weight: 142,
        oxygenLevel: 96,
        notes: 'Slight knee discomfort reported. Continue physiotherapy.',
        recordedBy: caregiver1._id,
        recordDate: new Date(now.getTime() - 1 * 24 * 60 * 60 * 1000),
      },
      {
        elder: elders[2]._id,
        bloodPressure: '122/80',
        heartRate: 70,
        temperature: 98.6,
        weight: 158,
        oxygenLevel: 98,
        notes: 'Hip mobility improving. Using walker less frequently.',
        recordedBy: caregiver2._id,
        recordDate: new Date(now.getTime() - 3 * 24 * 60 * 60 * 1000),
      },
      {
        elder: elders[3]._id,
        bloodPressure: '118/76',
        heartRate: 68,
        temperature: 98.2,
        weight: 130,
        bloodSugar: 95,
        oxygenLevel: 99,
        notes: 'All vitals excellent. No concerns.',
        recordedBy: caregiver2._id,
        recordDate: now,
      },
    ]);

    console.log('\n✅ Seed data inserted successfully!\n');
    console.log('Login credentials (password for all: 123456):\n');
    console.log('  Admin:     admin@eldercare.com');
    console.log('  Caregiver: james.care@eldercare.com');
    console.log('  Caregiver: maria.care@eldercare.com');
    console.log('  Elderly:   robert@eldercare.com');
    console.log('  Elderly:   eleanor@eldercare.com');
    console.log('  Elderly:   william@eldercare.com');
    console.log('  Elderly:   margaret@eldercare.com\n');

    process.exit(0);
  } catch (error) {
    console.error('Seed failed:', error.message);
    process.exit(1);
  }
};

seed();
