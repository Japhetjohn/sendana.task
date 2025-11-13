const EMAIL_API_ENDPOINT = import.meta.env.VITE_EMAIL_SERVICE_ENDPOINT;
const EMAIL_API_KEY = import.meta.env.VITE_EMAIL_SERVICE_API_KEY;

export const sendWelcomeEmail = async ({ email, firstName }) => {
  try {
    const response = await fetch(EMAIL_API_ENDPOINT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${EMAIL_API_KEY}`,
      },
      body: JSON.stringify({
        to: email,
        subject: "You're in! Let's make money move",
        html: generateWelcomeEmailHTML(firstName),
      }),
    });

    if (!response.ok) {
      throw new Error('Failed to send welcome email');
    }

    return await response.json();
  } catch (error) {
    console.error('Error sending welcome email:', error);
    throw error;
  }
};

const generateWelcomeEmailHTML = (firstName) => {
  return `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
    .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; }
    h1 { color: #111827; font-size: 24px; margin-bottom: 20px; }
    p { color: #4b5563; font-size: 16px; margin-bottom: 16px; }
    .features { margin: 30px 0; }
    .feature-item { margin: 12px 0; padding-left: 24px; }
    .cta-button { display: inline-block; background-color: #5f2dc4; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; }
    .footer { color: #6b7280; font-size: 14px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Hi ${firstName},</h1>
    <p>Welcome to Sendana, your new home for borderless banking. Think of us as the smarter, faster way to send, receive, and spend money across the globe.</p>
    <p><strong>Here's what you can do starting today:</strong></p>
    <div class="features">
      <div class="feature-item">Get paid from anywhere in the world</div>
      <div class="feature-item">Transfer funds to family, friends, or your own accounts</div>
      <div class="feature-item">Hold your balance in USDC to protect your earnings from devaluation</div>
    </div>
    <p>No complicated steps. No long paperwork. Just money that moves the way you need it to.</p>
    <p>Jump in and start exploring!</p>
    <a href="${window.location.origin}" class="cta-button">Get Started</a>
    <p>Welcome aboard,<br>The Sendana Team</p>
    <div class="footer">
      <p>*Sendana is not a bank. Banking services provided by licensed partners.</p>
    </div>
  </div>
</body>
</html>
  `;
};
