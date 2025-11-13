import express from 'express';
import { PrivyClient } from '@privy-io/server-auth';
import User from '../models/User.js';
import dotenv from 'dotenv';

dotenv.config();

const router = express.Router();

// Initialize Privy Client
const privyClient = new PrivyClient(
  process.env.PRIVY_APP_ID,
  process.env.PRIVY_APP_SECRET
);

// Middleware to verify Privy access token
const verifyPrivyToken = async (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({ error: 'No token provided' });
    }

    const token = authHeader.substring(7); // Remove 'Bearer ' prefix
    const verifiedClaims = await privyClient.verifyAuthToken(token);

    req.privyUser = verifiedClaims;
    next();
  } catch (error) {
    console.error('Token verification error:', error);
    return res.status(401).json({ error: 'Invalid token' });
  }
};

// POST /api/auth/login - Handle user login/signup
router.post('/login', verifyPrivyToken, async (req, res) => {
  try {
    const { userId } = req.privyUser;

    // Get user data from Privy
    const privyUser = await privyClient.getUser(userId);

    // Extract email from linked accounts
    let email = null;
    let authProvider = 'email';

    if (privyUser.google?.email) {
      email = privyUser.google.email;
      authProvider = 'google';
    } else if (privyUser.apple?.email) {
      email = privyUser.apple.email;
      authProvider = 'apple';
    } else if (privyUser.email?.address) {
      email = privyUser.email.address;
      authProvider = 'email';
    }

    if (!email) {
      return res.status(400).json({ error: 'No email found in Privy account' });
    }

    // Find or create user in database
    let user = await User.findOne({ privyId: userId });

    if (!user) {
      // Create new user
      user = new User({
        privyId: userId,
        email: email,
        authProvider: authProvider,
        profile: {
          name: privyUser.google?.name || privyUser.apple?.name || null,
          profilePicture: privyUser.google?.pictureUrl || null,
        },
      });
      await user.save();
    } else {
      // Update existing user
      user.email = email;
      user.authProvider = authProvider;
      user.updatedAt = Date.now();
      await user.save();
    }

    res.json({
      success: true,
      user: {
        id: user._id,
        privyId: user.privyId,
        email: user.email,
        authProvider: user.authProvider,
        profile: user.profile,
        balance: user.balance,
        stellarPublicKey: user.stellarPublicKey,
      },
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Server error during login' });
  }
});

// GET /api/auth/user - Get current user data
router.get('/user', verifyPrivyToken, async (req, res) => {
  try {
    const { userId } = req.privyUser;

    const user = await User.findOne({ privyId: userId });

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    res.json({
      success: true,
      user: {
        id: user._id,
        privyId: user.privyId,
        email: user.email,
        authProvider: user.authProvider,
        profile: user.profile,
        balance: user.balance,
        stellarPublicKey: user.stellarPublicKey,
      },
    });
  } catch (error) {
    console.error('Get user error:', error);
    res.status(500).json({ error: 'Server error fetching user' });
  }
});

// PUT /api/auth/user - Update user profile
router.put('/user', verifyPrivyToken, async (req, res) => {
  try {
    const { userId } = req.privyUser;
    const { name, profilePicture, stellarPublicKey, stellarSecretKey } = req.body;

    const user = await User.findOne({ privyId: userId });

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    if (name) user.profile.name = name;
    if (profilePicture) user.profile.profilePicture = profilePicture;
    if (stellarPublicKey) user.stellarPublicKey = stellarPublicKey;
    if (stellarSecretKey) user.stellarSecretKey = stellarSecretKey;

    await user.save();

    res.json({
      success: true,
      user: {
        id: user._id,
        privyId: user.privyId,
        email: user.email,
        authProvider: user.authProvider,
        profile: user.profile,
        balance: user.balance,
        stellarPublicKey: user.stellarPublicKey,
      },
    });
  } catch (error) {
    console.error('Update user error:', error);
    res.status(500).json({ error: 'Server error updating user' });
  }
});

export default router;
