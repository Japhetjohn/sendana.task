# Email Service Setup - Requirements

## What You Need to Provide

To enable the welcome email functionality, you need to set up an email service provider and provide the following in your `.env` file:

### Required Environment Variables:

```
VITE_EMAIL_SERVICE_ENDPOINT=your_email_api_endpoint
VITE_EMAIL_SERVICE_API_KEY=your_api_key
```

## Recommended Email Service Providers

### Option 1: Resend (Easiest - Recommended)

**Why Resend:**
- Modern API design
- Easy setup
- Good free tier (100 emails/day)
- Excellent deliverability

**Setup Steps:**
1. Go to https://resend.com and sign up
2. Verify your domain (or use their test domain for development)
3. Create an API key from the dashboard
4. Add to `.env`:
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.resend.com/emails
VITE_EMAIL_SERVICE_API_KEY=re_xxxxxxxxxxxxx
```

**API Format:** The code already handles Resend's format.

### Option 2: SendGrid

**Why SendGrid:**
- Reliable and established
- Good free tier (100 emails/day)
- Robust tracking and analytics

**Setup Steps:**
1. Go to https://sendgrid.com and sign up
2. Verify sender identity (email or domain)
3. Create an API key with Mail Send permissions
4. Add to `.env`:
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.sendgrid.com/v3/mail/send
VITE_EMAIL_SERVICE_API_KEY=SG.xxxxxxxxxxxxx
```

**Note:** You may need to modify `src/services/email.js` to match SendGrid's API format:
```javascript
body: JSON.stringify({
  personalizations: [{ to: [{ email }] }],
  from: { email: 'noreply@sendana.com', name: 'Sendana' },
  subject: subject,
  content: [{ type: 'text/html', value: html }]
}),
```

### Option 3: Mailgun

**Setup Steps:**
1. Sign up at https://mailgun.com
2. Verify your domain
3. Get API key from settings
4. Add to `.env`:
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.mailgun.net/v3/YOUR_DOMAIN/messages
VITE_EMAIL_SERVICE_API_KEY=your_api_key
```

**Note:** Mailgun uses form-data format, you'll need to modify the fetch call.

### Option 4: Custom Email Service

If you have your own email API, it should accept HTTP POST requests with this format:

**Request:**
```
POST /your-endpoint
Headers:
  Content-Type: application/json
  Authorization: Bearer YOUR_API_KEY

Body:
{
  "to": "user@example.com",
  "subject": "Email subject",
  "html": "<html>email content</html>"
}
```

**Response:**
Should return 200 OK on success.

## Email Content

The welcome email contains:

**Subject:** "You're in! Let's make money move"

**Content:**
- Personalized greeting with user's first name
- Welcome message
- Three key features:
  - Get paid from anywhere in the world
  - Transfer funds to family, friends, or accounts
  - Hold balance in USDC
- Call-to-action button linking back to login
- Sendana Team signature
- Legal disclaimer

## Testing Email Delivery

### Development Testing:
1. Use a test email address
2. Create account through the app
3. Check the email inbox (and spam folder)
4. Verify email renders correctly
5. Test the "Get Started" button link

### Production Checklist:
- [ ] Domain verified with email provider
- [ ] SPF and DKIM records configured
- [ ] Test email to multiple providers (Gmail, Outlook, Yahoo)
- [ ] Verify spam score is acceptable
- [ ] Test on mobile and desktop email clients
- [ ] Monitor email delivery rates
- [ ] Set up bounce and complaint handling

## Code Location

The email service code is in: `src/services/email.js`

Key functions:
- `sendWelcomeEmail({ email, firstName })` - Sends welcome email
- `generateWelcomeEmailHTML(firstName)` - Generates HTML template

## Troubleshooting

### Email not sending:

1. **Check API key:**
   - Verify it's correct in `.env`
   - Ensure no extra spaces
   - Check key has correct permissions

2. **Check sender email:**
   - Must be verified with provider
   - Update sender email in code if needed

3. **Check browser console:**
   - Look for fetch errors
   - Check response status codes

4. **Check email provider dashboard:**
   - Look for blocked emails
   - Check delivery logs
   - Verify account status

### Email goes to spam:

1. Set up SPF records for your domain
2. Set up DKIM signing
3. Warm up your sending reputation
4. Ensure content doesn't trigger spam filters
5. Add unsubscribe link if required

### Email formatting issues:

1. Test HTML in email testing tool (litmus.com)
2. Ensure inline CSS for compatibility
3. Test across email clients
4. Keep design simple and responsive

## Cost Estimates

**Free Tiers:**
- Resend: 100 emails/day, 3,000/month
- SendGrid: 100 emails/day
- Mailgun: 5,000 emails/month (first 3 months)

**Paid Plans (approximate):**
- Resend: $20/month for 50,000 emails
- SendGrid: $15/month for 40,000 emails
- Mailgun: $35/month for 50,000 emails

## Security Notes

- Never commit API keys to git
- Use environment variables
- Rotate keys regularly
- Monitor for unusual activity
- Set up rate limiting
- Implement retry logic for failures

## Next Steps After Setup

1. Fill in the `.env` file with your credentials
2. Run `npm install` to ensure dependencies are installed
3. Start dev server with `npm run dev`
4. Test account creation flow
5. Verify email is received
6. Check email renders correctly
7. Test on different email clients

## Support Resources

- Resend: https://resend.com/docs
- SendGrid: https://docs.sendgrid.com
- Mailgun: https://documentation.mailgun.com

## Contact

If you encounter issues with the email integration, check:
1. Browser console for errors
2. Email provider dashboard for logs
3. Network tab in developer tools
4. `.env` file configuration
