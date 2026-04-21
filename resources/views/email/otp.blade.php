<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0fdf4;
            padding: 40px 16px;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper {
            max-width: 520px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header-logo {
            display: inline-block;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-radius: 16px;
            padding: 14px 24px;
            margin-bottom: 0;
        }
        .header-logo span {
            font-size: 28px;
        }
        .header-logo h1 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-top: 6px;
        }
        .header-logo p {
            color: rgba(255,255,255,0.75);
            font-size: 11px;
            margin-top: 2px;
        }

        /* Card */
        .card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(22, 163, 74, 0.12);
            margin-top: 0;
        }

        /* Card top bar */
        .card-topbar {
            height: 5px;
            background: linear-gradient(90deg, #16a34a, #22c55e, #4ade80);
        }

        .card-body {
            padding: 36px 40px 32px;
        }

        .greeting {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 8px;
        }
        .greeting span {
            color: #16a34a;
        }

        .intro-text {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* OTP box */
        .otp-section {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px dashed #22c55e;
            border-radius: 16px;
            text-align: center;
            padding: 28px 20px;
            margin-bottom: 28px;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #16a34a;
            margin-bottom: 12px;
        }
        .otp-code {
            font-size: 52px;
            font-weight: 900;
            letter-spacing: 14px;
            color: #15803d;
            font-family: 'Courier New', monospace;
            line-height: 1;
            text-indent: 14px; /* compensate letter-spacing on last char */
        }
        .otp-expires {
            margin-top: 12px;
            font-size: 12px;
            color: #6b7280;
        }
        .otp-expires strong {
            color: #dc2626;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 24px 0;
        }

        /* Steps */
        .steps-title {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
        }
        .steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        .step-num {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #16a34a;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        /* Warning */
        .warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 24px;
            font-size: 12.5px;
            color: #92400e;
            line-height: 1.6;
        }
        .warning strong {
            display: block;
            margin-bottom: 2px;
            font-size: 13px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px 40px 28px;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            font-size: 11.5px;
            color: #9ca3af;
            line-height: 1.7;
        }
        .footer .app-name {
            font-weight: 700;
            color: #16a34a;
        }
        .footer .timestamp {
            display: inline-block;
            margin-top: 6px;
            background: #e5e7eb;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header Logo -->
        <div class="header">
            <div class="header-logo">
                <span>🍛</span>
                <h1>Angkringan Asli Klaten</h1>
                <p>Sistem Manajemen Angkringan</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-topbar"></div>
            <div class="card-body">

                <h2 class="greeting">Verifikasi <span>OTP</span> Anda</h2>
                <p class="intro-text">
                    Halo! Kami menerima permintaan login ke sistem Angkringan Asli Klaten.
                    Gunakan kode di bawah ini untuk menyelesaikan proses verifikasi.
                </p>

                <!-- OTP Box -->
                <div class="otp-section">
                    <div class="otp-label">🔐 Kode OTP Anda</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expires">
                        Kode berlaku selama <strong>5 menit</strong> sejak email ini dikirim
                    </div>
                </div>

                <!-- Steps -->
                <p class="steps-title">Cara menggunakan kode OTP:</p>
                <ul class="steps">
                    <li class="step-item">
                        <span class="step-num">1</span>
                        <span>Buka halaman verifikasi OTP yang muncul setelah login.</span>
                    </li>
                    <li class="step-item">
                        <span class="step-num">2</span>
                        <span>Masukkan 6 digit kode OTP di atas ke dalam kolom yang tersedia.</span>
                    </li>
                    <li class="step-item">
                        <span class="step-num">3</span>
                        <span>Klik tombol <strong>Verifikasi</strong> untuk masuk ke sistem.</span>
                    </li>
                </ul>

                <hr class="divider">

                <!-- Warning -->
                <div class="warning">
                    <strong>⚠️ Peringatan Keamanan</strong>
                    Jangan pernah membagikan kode OTP ini kepada siapapun, termasuk pihak yang mengaku sebagai admin.
                </div>

            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    Email ini dikirim otomatis oleh <span class="app-name">Angkringan Asli Klaten</span>.<br>
                    Jangan balas email ini.
                </p>
                <span class="timestamp">📅 {{ now()->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>

    </div>
</body>
</html>
