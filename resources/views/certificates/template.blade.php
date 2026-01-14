<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pegawai Terbaik</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }
        .certificate-container {
            width: 297mm;
            height: 210mm;
            padding: 20mm;
            box-sizing: border-box;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 10px solid #1a365d;
        }
        .certificate-inner {
            width: 100%;
            height: 100%;
            background: white;
            border: 3px solid #1a365d;
            padding: 40px;
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }
        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1a365d;
            font-size: 32px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header h2 {
            color: #2c5282;
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 20px 0;
            text-transform: uppercase;
        }
        .divider {
            width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #1a365d, transparent);
            margin: 0 auto 30px auto;
        }
        .category {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 20px;
            font-style: italic;
        }
        .recipient {
            margin: 40px 0;
        }
        .recipient-label {
            font-size: 16px;
            color: #718096;
            margin-bottom: 10px;
        }
        .recipient-name {
            font-size: 36px;
            color: #1a365d;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .recipient-details {
            font-size: 18px;
            color: #4a5568;
            margin: 5px 0;
        }
        .period {
            margin: 30px 0;
            font-size: 18px;
            color: #2d3748;
        }
        .period-label {
            font-weight: bold;
        }
        .content {
            font-size: 16px;
            color: #4a5568;
            line-height: 1.8;
            margin: 20px 0;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .qr-section {
            text-align: center;
        }
        .qr-section img {
            width: 120px;
            height: 120px;
            border: 2px solid #1a365d;
            padding: 5px;
            background: white;
        }
        .qr-section p {
            font-size: 12px;
            color: #718096;
            margin: 10px 0 0 0;
        }
        .signature-section {
            text-align: center;
            margin-right: 80px;
        }
        .signature-label {
            font-size: 14px;
            color: #718096;
            margin-bottom: 60px;
        }
        .signature-name {
            font-size: 16px;
            color: #1a365d;
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-title {
            font-size: 14px;
            color: #4a5568;
            margin-top: 5px;
        }
        .date {
            font-size: 14px;
            color: #4a5568;
            text-align: left;
            margin-left: 100px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 100px;
            color: rgba(26, 54, 93, 0.03);
            font-weight: bold;
            text-transform: uppercase;
            z-index: 0;
            pointer-events: none;
        }
        .border-decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        .corner {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 3px solid #1a365d;
        }
        .corner-tl {
            top: 10px;
            left: 10px;
            border-right: none;
            border-bottom: none;
        }
        .corner-tr {
            top: 10px;
            right: 10px;
            border-left: none;
            border-bottom: none;
        }
        .corner-bl {
            bottom: 10px;
            left: 10px;
            border-right: none;
            border-top: none;
        }
        .corner-br {
            bottom: 10px;
            right: 10px;
            border-left: none;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-inner">
            <div class="watermark">Sertifikat</div>

            <div class="border-decoration">
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>
            </div>

            <div class="header">
                @if(file_exists(public_path('images/logo-pa.png')))
                    <img src="{{ asset('images/logo-pa.png') }}" alt="Logo PA Penajam" style="max-height: 80px; margin-bottom: 20px;">
                @endif
                <h1>Pengadilan Agama Penajam</h1>
                <div class="divider"></div>
                <h2>Sertifikat Pegawai Terbaik</h2>
            </div>

            <div class="category">
                Kategori: {{ $category->nama }}
            </div>

            <div class="recipient">
                <div class="recipient-label">Diberikan kepada:</div>
                <div class="recipient-name">{{ $employee->nama }}</div>
                <div class="recipient-details">
                    NIP: {{ $employee->nip }}<br>
                    {{ $employee->jabatan }}
                </div>
            </div>

            <div class="content">
                Atas prestasinya sebagai pegawai terbaik pada kategori<br>
                <strong>{{ $category->nama }}</strong> dengan perolehan skor <strong>{{ number_format($score, 2) }}</strong>
            </div>

            <div class="period">
                <span class="period-label">Periode:</span> {{ $period->name }}
                ({{ ucfirst($period->semester) }} {{ $period->year }})
            </div>

            <div class="footer">
                <div class="date">
                    Penajam, {{ $issuedDate }}
                </div>

                <div class="qr-section">
                    <img src="{{ $qrCodeDataUrl }}" alt="QR Code Verifikasi">
                    <p>Scan untuk verifikasi</p>
                </div>

                <div class="signature-section">
                    <div class="signature-label">Ketua Pengadilan Agama Penajam</div>
                    <div class="signature-name">Dr. H. Muhammad Syafi'i, S.H.I., M.H.I.</div>
                    <div class="signature-title">NIP. 19700512 199503 1 002</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
