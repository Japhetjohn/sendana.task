import mongoose from 'mongoose';

const userSchema = new mongoose.Schema({
  privyId: {
    type: String,
    required: true,
    unique: true,
  },
  email: {
    type: String,
    required: true,
    unique: true,
  },
  walletAddress: {
    type: String,
    default: null,
  },
  stellarPublicKey: {
    type: String,
    default: null,
  },
  stellarSecretKey: {
    type: String,
    default: null,
  },
  authProvider: {
    type: String,
    enum: ['email', 'google', 'apple'],
    default: 'email',
  },
  profile: {
    name: String,
    profilePicture: String,
  },
  balance: {
    type: Number,
    default: 0,
  },
  transactions: [{
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Transaction',
  }],
  createdAt: {
    type: Date,
    default: Date.now,
  },
  updatedAt: {
    type: Date,
    default: Date.now,
  },
});

// Update the updatedAt field before saving
userSchema.pre('save', function(next) {
  this.updatedAt = Date.now();
  next();
});

const User = mongoose.model('User', userSchema);

export default User;
