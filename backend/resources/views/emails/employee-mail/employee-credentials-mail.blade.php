<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Employee Credentials</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding: 20px 0;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 25px; background-color:#143a60;">
                            @if ($data['setting']->company_logo_path)
                                <img src="{{ $message->embed(public_path('storage/' . $data['setting']->company_logo_path)) }}"
                                    alt="Company Logo" width="140" style="display:block;">
                            @endif

                            <h2 style="color:#ffffff; margin:10px 0 0;">
                                {{ $data['setting']->company_name }}
                            </h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333;">

                            <p style="margin:0 0 15px;">Hello <strong>{{ $data['employee']['name'] }}</strong>,</p>

                            <p style="margin:0 0 20px;">
                                Your employee account has been successfully created.
                                Please use the credentials below to access your account.
                            </p>

                            <!-- Credentials Box -->
                            <table width="100%" cellpadding="10" cellspacing="0"
                                style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td><strong>Login URL:</strong></td>
                                    <td><a href="{{ $data['login_url'] }}">{{ $data['login_url'] }}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $data['employee']['email'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Password:</strong></td>
                                    <td>{{ $data['employee']['password'] }}</td>
                                </tr>
                            </table>

                            <!-- Quick Access Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0;">
                                <tr>
                                    <td style="font-weight:bold; padding-bottom:8px;">
                                        QUICK ACCESS
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb;"></td>
                                </tr>
                                <tr>
                                    <td style="padding-top:12px; color:#374151;">
                                        <div style="margin-bottom:6px;">→ Login to your dashboard</div>
                                        <div style="margin-bottom:6px;">→ Manage your candidates</div>
                                        <div>→ Access system features</div>
                                    </td>
                                </tr>
                            </table>


                            <!-- CTA Button -->
                            <div style="text-align:center; margin:30px 0;">
                                <a href="{{ $data['login_url'] }}"
                                    style="background:#2a9df4; color:#ffffff; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block;">
                                    Login to Dashboard
                                </a>
                            </div>

                            <!-- Security Notice -->
                            <p style="margin:0 0 10px;"><strong>Security Tips:</strong></p>
                            <ul style="padding-left:20px; margin:0 0 20px;">
                                <li>Change your password after first login</li>
                                <li>Do not share your credentials</li>
                                <li>Always logout from shared devices</li>
                            </ul>

                            <p>If you face any issues, feel free to contact our support team.</p>

                            <!-- Support Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0;">
                                <tr>
                                    <td style="font-weight:bold; padding-bottom:8px;">
                                        SUPPORT
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb;"></td>
                                </tr>
                                <tr>
                                    <td style="padding-top:12px; color:#374151;">

                                        <div style="margin-bottom:6px;">
                                            <strong>Email:</strong>
                                            {{ $data['setting']->company_email ?? 'support@example.com' }}
                                        </div>

                                        <div>
                                            <strong>Phone:</strong>
                                            {{ $data['setting']->company_phone ?? '+880-XXX-XXXXXX' }}
                                        </div>

                                    </td>
                                </tr>
                            </table>


                            <p style="margin-top:30px;">
                                Regards,<br>
                                <strong>{{ $data['setting']->company_name }}</strong>
                            </p>

                        </td>
                        <!-- Footer -->
                    <tr>
                        <td style=" padding:20px; text-align:center; font-size:12px; color:#6b7280;">
                            <p style="margin:0;">
                                © {{ date('Y') }} {{ $data['setting']->company_name }}. All rights reserved.
                            </p>

                            <p style="margin:5px 0 0;">
                                This is an automated email. Please do not reply.
                            </p>
                        </td>
                    </tr>
        </tr>



    </table>

    </td>
    </tr>
    </table>

</body>

</html>
