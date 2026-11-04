<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP - TERMINAL</title>
    <style>
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }

        .container {
            max-width: 520px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 30px 24px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }

        .body {
            padding: 32px 28px;
        }

        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .description {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }

        .otp-box {
            background: #f8fafc;
            border: 2px dashed #424dc4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }

        .otp-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #424dc4;
            margin: 0;
        }

        .notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 12px;
            line-height: 1.5;
            color: #92400e;
            margin-bottom: 24px;
        }

        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>TERMINAL</h1>
            <p>Tempat Eksperimen Riset dan Inovasi Digital • UIN Sultan Maulana Hasanuddin Banten</p>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="greeting">
                Halo, {{ $userName }}!
            </div>
            <div class="description">
                Sistem mendeteksi permintaan masuk ke akun Anda (NIM/NIP: <strong>{{ $userNim }}</strong>). Gunakan 6
                digit Kode OTP di bawah ini untuk menyelesaikan otentikasi login:
            </div>

            <!-- OTP Box -->
            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi Masuk (OTP)</div>
                <div class="otp-code">{{ $otpCode }}</div>
            </div>

            <!-- Notice -->
            <div class="notice">
                ⚠️ <strong>PENTING:</strong> Kode ini hanya berlaku selama <strong>5 menit</strong>. Jangan pernah
                membagikan kode OTP ini kepada siapa pun demi keamanan akun laboratorium Anda.
            </div>

            <div class="description" style="font-size: 12px; color: #64748b; margin-bottom: 0;">
                Jika Anda tidak merasa melakukan permintaan login ini, abaikan email ini atau segera hubungi Kepala
                Laboratorium.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Email ini dikirim secara otomatis oleh Sistem Reservasi & Billing TERMINAL.<br>
            &copy; {{ date('Y') }} TERMINAL • Tempat Eksperimen Riset dan Inovasi Digital • All rights reserved.
        </div>
    </div>
</body>

</html>