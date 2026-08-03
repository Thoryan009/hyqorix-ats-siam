<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $billNo }}</title>
</head>

<body style="font-family: Helvetica, Arial, sans-serif; background-color:#f4f4f4; margin:0; padding:0; color:#333;">

    <div
        style="max-width:600px; margin:30px auto; background-color:#ffffff; border-radius:8px; overflow:hidden; padding:20px 30px;">

        <!-- Header / Branding -->
        <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid #ddd;">
            <img src="{{ $message->embed(public_path('storage/' . $setting->company_logo_path)) }}" alt="Logo"
                style="max-height:50px;">

            <h2 style="margin:0; font-size:24px; color:#1a73e8;">
                Invoice #{{ $billNo }}
            </h2>
        </div>

        <!-- Body -->
        <div style="margin-top:20px; line-height:1.6; font-size:15px;">
            {!! nl2br(e($body)) !!}
        </div>

        <!-- Footer -->
        <div
            style="margin-top:30px; font-size:12px; color:#666; border-top:1px solid #ddd; padding-top:15px; text-align:center;">
            <p style="margin:0 0 10px 0;">
                {{ $setting->company_name }} | {{ $setting->company_address }}
            </p>

            <p style="margin:0 0 10px 0;">
                <a href="mailto:{{ $setting->company_email }}" style="color:#1a73e8; text-decoration:none;">
                    {{ $setting->company_email }}
                </a>
                | {{ $setting->company_phone }}
            </p>

            <p style="margin:0;">
                This is an automated email. Please do not reply directly.
            </p>
        </div>

    </div>

</body>

</html>