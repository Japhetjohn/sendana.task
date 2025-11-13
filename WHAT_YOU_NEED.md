# What You Need to Provide for Email Functionality

## Quick Checklist

To make the welcome email work, you need to provide **2 things**:

### 1. Email Service API Endpoint

This is the URL where emails will be sent.

**Example for Resend:**
```
https://api.resend.com/emails
```

**Example for SendGrid:**
```
https://api.sendgrid.com/v3/mail/send
```

### 2. Email Service API Key

This is your authentication key from the email provider.

**Example formats:**
- Resend: `re_123abc456def789...`
- SendGrid: `SG.123abc456def789...`
- Mailgun: `key-123abc456def789...`

## Where to Add These

Put both values in the `.env` file in the root directory:

```
VITE_EMAIL_SERVICE_ENDPOINT=your_endpoint_here
VITE_EMAIL_SERVICE_API_KEY=your_api_key_here
```

## Easiest Option: Resend (Recommended)

**Why:** Simple setup, free tier, excellent deliverability

**Steps:**
1. Go to https://resend.com
2. Click "Sign Up" (free account)
3. Verify your email
4. Go to "API Keys" in dashboard
5. Click "Create API Key"
6. Copy the key (starts with `re_`)
7. Add to .env:
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.resend.com/emails
VITE_EMAIL_SERVICE_API_KEY=re_your_key_here
```

**Time needed:** 5 minutes

## That's It!

Once you add those 2 values to `.env`, the welcome email will automatically send when users sign up.

## Testing

1. Add the values to `.env`
2. Run `npm run dev`
3. Create a test account
4. Check your email inbox
5. You should receive the welcome email

## Need Help?

See `EMAIL_SETUP.md` for:
- Detailed setup instructions
- Multiple provider options
- Troubleshooting guide
- Alternative solutions

## Free Tiers

All recommended providers have free tiers suitable for testing:
- Resend: 100 emails/day (3,000/month)
- SendGrid: 100 emails/day
- Mailgun: 5,000 emails/month

## Email Content Preview

**Subject:** You're in! Let's make money move

**Body includes:**
- Personalized greeting
- Welcome message
- Three key features of Sendana
- "Get Started" button
- Sendana Team signature
- Legal disclaimer

The email is already professionally designed and matches Sendana's branding.
