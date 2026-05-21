<x-mail::message>
# Welcome to {{ config('app.name', 'JobPortal') }}, {{ $name }}!

Thank you for starting your onboarding with us. To verify your email address and secure your new account, please use the 6-digit one-time password (OTP) provided below:

<div style="background-color: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0;">
<span style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #6d28d9; text-shadow: 1px 1px 0px #ede9fe;">{{ $otp }}</span>
</div>

> [!NOTE]
> This code will expire in **15 minutes**. For security reasons, please do not share this code with anyone.

If you did not request this code, you can safely ignore this email.

Best regards,  
The **{{ config('app.name', 'JobPortal') }}** Team
</x-mail::message>
