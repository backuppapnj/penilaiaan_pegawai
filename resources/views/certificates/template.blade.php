<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Penghargaan</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Times New Roman', Times, serif;
            -webkit-print-color-adjust: exact;
        }

        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        /* Watermark Logo Style */
        .watermark-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            text-align: center;
            padding-top: 15mm; /* Dorong ke bawah agar optik center */
        }

        .watermark-container::before {
            content: '';
            display: inline-block;
            height: 100%;
            vertical-align: middle;
        }

        .watermark-logo {
            width: 100mm;
            opacity: 0.05;
            vertical-align: middle;
            display: inline-block;
        }

        .content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            text-align: center;
            box-sizing: border-box;
        }

        .main-content {
            margin-top: 68mm;
            padding: 0 15mm;
        }

        .institution-header {
            color: #1a365d;
            font-size: 18pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4mm 0;
            font-weight: 900;
            text-shadow: 1px 1px 0px rgba(255,255,255,0.8);
        }

        h2 {
            color: #b79c5a;
            font-size: 24pt;
            text-transform: uppercase;
            margin: 0 0 3mm 0;
            font-family: serif;
            font-weight: normal;
            letter-spacing: 3px;
            padding: 0;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: '';
            display: block;
            width: 60%;
            height: 2px;
            background: #b79c5a;
            margin: 1.5mm auto 0;
        }

        .category {
            font-size: 13pt;
            color: #2d3748;
            margin-top: 0;
            margin-bottom: 3mm;
            font-style: italic;
            font-weight: 500;
        }

        .recipient-label {
            margin-top: 0;
            margin-bottom: 1.5mm;
            font-size: 12pt;
            color: #4a5568;
            font-style: italic;
            letter-spacing: 1px;
        }

        .recipient-name {
            font-size: 26pt;
            color: #1a365d;
            font-weight: bold;
            margin: 1.5mm 0;
            text-transform: uppercase;
            font-family: 'Times New Roman', serif;
            text-shadow: 1px 1px 0px rgba(255,255,255,1);
            line-height: 1.2;
        }

        .recipient-detail {
            font-size: 11pt;
            color: #2d3748;
            margin-top: 1.5mm;
            margin-bottom: 2mm;
            font-weight: 500;
            line-height: 1.3;
        }

        .description {
            margin: 2mm auto;
            width: 85%;
            font-size: 11pt;
            line-height: 1.35;
            color: #1a202c;
        }

        .score-box {
            display: inline-block;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid #b79c5a;
            padding: 3px 25px;
            border-radius: 50px;
            font-weight: bold;
            color: #1a365d;
            margin-top: 5px;
            font-size: 11pt;
        }

        /* Footer Section - Centered */
        .footer-section {
            position: absolute;
            bottom: 25mm;
            left: 0;
            width: 100%;
            text-align: center;
        }

        .signature-wrapper {
            display: inline-block;
            text-align: center;
            min-width: 300px;
        }

        .date-text {
            margin-bottom: 1mm;
            color: #2d3748;
            font-size: 12pt;
        }

        .role-text {
            font-weight: bold;
            color: #1a365d;
            font-size: 13pt;
            margin-bottom: 16mm;
        }

        .signature-line {
            width: 250px;
            margin: 0 auto;
            border-bottom: 2px solid #1a365d;
            margin-bottom: 2px;
        }

        .sign-name {
            font-weight: bold;
            color: #1a365d;
            font-size: 14pt;
            text-transform: uppercase;
        }

        .sign-nip {
            font-size: 11pt;
            color: #4a5568;
            letter-spacing: 1px;
        }

    </style>
</head>
<body>
    <img src="{{ $backgroundDataUrl }}" class="background-image" alt="Background">
    
    <!-- Watermark Logo -->
    @if(isset($logoDataUrl) && $logoDataUrl)
        <div class="watermark-container">
            <img src="{{ $logoDataUrl }}" class="watermark-logo" alt="Watermark">
        </div>
    @elseif(file_exists(public_path('images/logo-pa.png')))
        <div class="watermark-container">
            <img src="{{ asset('images/logo-pa.png') }}" class="watermark-logo" alt="Watermark">
        </div>
    @endif
    
    <div class="content">
        <div class="main-content">
            <h2>{{ $institution_name }}</h2>
            <div class="category">
                Kategori: <strong>{{ $category->nama }}</strong>
            </div>

            <!-- Recipient -->
            <div class="recipient-label">Diberikan kepada:</div>
            
            <div class="recipient-name">{{ $employee->nama }}</div>
            
            <div class="recipient-detail">
                NIP. {{ $employee->nip }}<br>
                {{ $employee->jabatan }}
            </div>

            <!-- Description -->
            <div class="description">
                Atas dedikasi, integritas, dan kinerja luar biasa yang telah ditunjukkan sebagai Pegawai Terbaik<br>
                pada Periode <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
                <br>
                <div class="score-box">
                    Rata-rata Nilai: {{ number_format($score,2,",",".")  }}
                </div>
            </div>
        </div>

        <!-- Footer Centered -->
        <div class="footer-section">
            <div class="signature-wrapper">
                <div class="date-text">Penajam, {{ $issuedDate }}</div>
                <div class="role-text">{{ $chairman_role }}</div>
                
                <div class="signature-line"></div>
                
                <div class="sign-name">{{ $chairman_name }}</div>
                <div class="sign-nip">NIP. {{ $chairman_nip }}</div>
            </div>
        </div>
    </div>
</body>
</html>
