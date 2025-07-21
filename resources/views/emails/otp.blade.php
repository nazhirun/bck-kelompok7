<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'reset_password' ? 'Kode Reset Password' : 'Kode Verifikasi Anda' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            border: 1px solid #e9e9e9;
        }

        .email-header {
            background-color: #004AAD; /* Ganti dengan warna utama brand Anda */
            padding: 25px;
            text-align: center;
        }

        .email-header img {
            max-width: 100px; /* Sesuaikan ukuran logo Anda */
        }

        .email-body {
            padding: 30px 40px;
            line-height: 1.6;
        }

        .email-body h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 0;
        }

        .email-body p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 20px;
        }

        .otp-box {
            background-color: #eef2ff; /* Warna latar yang lembut */
            border-radius: 8px;
            color: #004AAD; /* Warna utama brand Anda */
            font-size: 40px;
            font-weight: 700;
            letter-spacing: 8px;
            padding: 15px 20px;
            text-align: center;
            margin: 30px 0;
            border: 2px dashed #b4c8ff;
        }

        .warning-text {
            font-size: 14px;
            color: #888888;
            text-align: center;
        }

        .email-footer {
            background-color: #f4f7f6;
            padding: 20px 40px;
            font-size: 12px;
            color: #999999;
            text-align: center;
        }

        .email-footer a {
            color: #004AAD; /* Warna utama brand Anda */
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://assets.nsd.co.id/images/kampus/logo/STMIK-Jayanusa-Padang.png" alt="Logo MY-ATK JAYANUSA">
            </div>

        <div class="email-body">
            @if($type === 'reset_password')
                <h1>Reset Password</h1>
                
                <p>Halo {{ $name ?? 'Pengguna' }},</p>
                
                <p>Kami menerima permintaan reset password untuk akun Anda di <strong>MY-ATK JAYANUSA</strong>. Gunakan kode di bawah ini untuk melanjutkan proses reset password. Kode ini bersifat rahasia, jangan bagikan kepada siapapun.</p>
            @else
                <h1>Satu Langkah Lagi!</h1>
                
                <p>Halo {{ $name ?? 'Pengguna' }},</p>
                
                <p>Gunakan kode di bawah ini untuk memverifikasi akun Anda di <strong>MY-ATK JAYANUSA</strong>. Jangan bagikan kode ini kepada siapapun.</p>
            @endif
            
            <div class="otp-box">{{ $otp ?? '123456' }}</div>
            
            <p class="warning-text">Kode ini hanya valid selama <strong>10 menit</strong>. Jika Anda tidak merasa meminta kode ini, mohon abaikan email ini.</p>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} MY-ATK JAYANUSA. Semua Hak Cipta Dilindungi.</p>
            <p>Email ini dibuat secara otomatis, mohon tidak membalas.</p>
        </div>
    </div>
</body>
</html>