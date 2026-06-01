<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $appName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f4f6f8;
            color: #1a1a2e;
            padding: 40px 16px;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        .email-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6cdf 100%);
            border-radius: 12px 12px 0 0;
            padding: 40px 40px 32px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .email-header p.tagline {
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin-top: 6px;
        }

        .email-body {
            background-color: #ffffff;
            padding: 40px;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 12px;
        }

        .description {
            font-size: 15px;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .info-card {
            background-color: #f7faff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 24px 28px;
            margin-bottom: 28px;
        }

        .info-card h2 {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #2d6cdf;
            margin-bottom: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #1a1a2e;
            font-weight: 600;
            margin-left: 4px;
        }

        .cta-button {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6cdf 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 8px;
            margin-bottom: 28px;
            letter-spacing: 0.3px;
        }

        .disclaimer {
            font-size: 13px;
            color: #a0aec0;
            line-height: 1.6;
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 18px;
        }

        .disclaimer strong {
            color: #92400e;
        }

        .email-footer {
            background-color: #f7faff;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 24px 40px;
            text-align: center;
        }

        .email-footer p {
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.8;
        }

        .email-footer a {
            color: #2d6cdf;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">

        {{-- Header --}}
        <div class="email-header">
            <h1>🎉 Welcome to {{ $appName }}</h1>
            <p class="tagline">Your account and company are ready to go</p>
        </div>

        {{-- Body --}}
        <div class="email-body">

            <p class="greeting">
                Hello, {{ $user['user_fullname'] ?? $authUser->auth_user_username }}!
            </p>

            <p class="description">
                Your registration was successful. Below are the details of your new account and company.
                Please keep this information safe.
            </p>

            {{-- Account Info --}}
            <div class="info-card">
                <h2>Account Details</h2>

                <div class="info-row">
                    <span class="info-label">Username</span>
                    <span class="info-value">{{ $authUser->auth_user_username }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $authUser->auth_user_email }}</span>
                </div>

                @if (!empty($company['company_name']))
                <div class="info-row">
                    <span class="info-label">Company</span>
                    <span class="info-value">{{ $company['company_name'] }}</span>
                </div>
                @endif

                @if (!empty($company['company_slug']))
                <div class="info-row">
                    <span class="info-label">Company Slug</span>
                    <span class="info-value">{{ $company['company_slug'] }}</span>
                </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Registered At</span>
                    <span class="info-value">{{ now()->format('d M Y, H:i') }} (UTC)</span>
                </div>
            </div>

            {{-- CTA --}}
            <a href="{{ $verifyUrl }}" class="cta-button">
                Verify Email Address →
            </a>

            {{-- Disclaimer --}}
            <div class="disclaimer">
                <strong>⚠️ Security Notice:</strong>
                If you did not create this account, please ignore this email or contact our support team immediately.
                Never share your password with anyone.
            </div>

        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <p>
                This email was sent automatically by <strong>{{ $appName }}</strong>.<br>
                Please do not reply to this email.<br>
                &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
            </p>
        </div>

    </div>
</body>
</html>
