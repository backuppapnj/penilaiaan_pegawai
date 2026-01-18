<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pegawai Terdisiplin</title>
    <style>
        /* Menggunakan style yang sama persis dengan template utama agar konsisten */
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
            background-color: #ffffff;
            box-sizing: border-box;
        }
        
        *, *:before, *:after {
            box-sizing: border-box;
        }

        .page-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            background: #fff;
            overflow: hidden;
        }

        .outer-frame {
            position: absolute;
            top: 10mm;
            left: 10mm;
            width: 277mm;
            height: 190mm;
            border: 3mm solid #1a365d;
            background: white;
            z-index: 10;
        }

        .gold-container {
            position: absolute;
            top: 4mm; 
            left: 4mm;
            width: 269mm;
            height: 182mm;
            z-index: 11;
        }

        .inner-frame {
            width: 100%;
            height: 100%;
            border: 1px solid #b79c5a;
            position: relative;
        }

        .corner {
            position: absolute;
            width: 25mm;
            height: 25mm;
            border: 4px double #1a365d;
            z-index: 12;
        }

        .corner-tl { top: -2mm; left: -2mm; border-right: none; border-bottom: none; }
        .corner-tr { top: -2mm; right: -2mm; border-left: none; border-bottom: none; }
        .corner-bl { bottom: -2mm; left: -2mm; border-right: none; border-top: none; }
        .corner-br { bottom: -2mm; right: -2mm; border-left: none; border-top: none; }

        .content {
            position: absolute;
            top: 12mm;
            left: 20mm;
            right: 20mm;
            bottom: 15mm;
            text-align: center;
            z-index: 20;
        }

        .logo {
            height: 22mm;
            margin-bottom: 2mm;
        }

        h1 {
            color: #1a365d;
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 1mm 0;
            font-weight: bold;
        }

        h2 {
            color: #b79c5a;
            font-size: 24pt;
            text-transform: uppercase;
            margin: 3mm 0;
            font-family: serif;
            font-weight: normal;
            letter-spacing: 3px;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            padding: 1mm 8mm;
        }

        .category {
            font-size: 12pt;
            color: #1a365d;
            margin-top: 1mm;
            font-style: italic;
        }

        .recipient-label {
            margin-top: 6mm;
            font-size: 11pt;
            color: #666;
            font-style: italic;
        }

        .recipient-name {
            font-size: 28pt;
            color: #1a365d;
            font-weight: bold;
            margin: 1mm 0;
            text-transform: uppercase;
            font-family: 'Times New Roman', serif;
        }

        .recipient-detail {
            font-size: 12pt;
            color: #4a5568;
        }

        .description {
            margin: 4mm auto;
            width: 85%;
            font-size: 11pt;
            line-height: 1.4;
            color: #2d3748;
        }

        .score-box {
            display: inline-block;
            background: #f7fafc;
            border: 1px dashed #b79c5a;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: #1a365d;
            margin-top: 3px;
            font-size: 10pt;
        }

        .footer-table {
            width: 100%;
            margin-top: 8mm;
        }
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
        }
        
        .signature-wrapper {
            display: inline-block;
            text-align: center;
            width: 100%;
        }

        .signature-line {
            width: 80%;
            margin: 0 auto;
            border-bottom: 1.5px solid #1a365d;
            margin-bottom: 5px;
            margin-top: 20mm;
        }

        .sign-name {
            font-weight: bold;
            color: #1a365d;
            font-size: 12pt;
            display: block;
            width: 100%;
        }
        .sign-nip {
            font-size: 10pt;
            color: #666;
        }
        .date-text {
            margin-bottom: 1mm;
            color: #4a5568;
            font-size: 11pt;
        }

        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70mm;
            opacity: 0.07;
            z-index: 5;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="outer-frame">
            <div class="gold-container">
                <div class="inner-frame"></div>
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>
            </div>

            @if(isset($logoDataUrl) && $logoDataUrl)
                <img src="{{ $logoDataUrl }}" class="watermark-logo" alt="Watermark">
            @endif

            <div class="content">
                @if(isset($logoDataUrl) && $logoDataUrl)
                    <img src="{{ $logoDataUrl }}" class="logo" alt="Logo Instansi">
                @elseif(file_exists(public_path('images/logo-pa.png')))
                    <img src="{{ asset('images/logo-pa.png') }}" class="logo" alt="Logo">
                @endif
                
                <h1>{{ $institution_name }}</h1>
                
                <h2>Sertifikat Kedisiplinan</h2>
                
                <div class="category">
                    Kategori: <strong>{{ $category->nama }}</strong>
                </div>

                <div class="recipient-label">Diberikan kepada:</div>
                <div class="recipient-name">{{ $employee->nama }}</div>
                <div class="recipient-detail">
                    NIP. {{ $employee->nip }} | {{ $employee->jabatan }}
                </div>

                <div class="description">
                    Atas konsistensi dan kedisiplinan kerja yang luar biasa sebagai Pegawai Terdisiplin<br>
                    pada Periode <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
                    <br>
                    <div class="score-box">
                        Total Skor Disiplin: {{ number_format($score, 2) }}
                    </div>
                </div>

                <table class="footer-table">
                    <tr>
                        <td style="width: 30%;">
                            <img src="{{ $qrCodeDataUrl }}" style="width: 22mm; border: 1px solid #ddd; padding: 2px;" alt="QR">
                            <div style="font-size: 8pt; margin-top: 2px; color: #888;">ID: {{ $certificateId }}</div>
                        </td>
                        <td style="width: 25%;"></td>
                        <td style="width: 45%;">
                            <div class="signature-wrapper">
                                <div class="date-text">Penajam, {{ $issuedDate }}</div>
                                <div style="font-weight: bold; color: #1a365d;">{{ $chairman_role }}</div>
                                
                                <div class="signature-line"></div>
                                
                                <div class="sign-name">{{ $chairman_name }}</div>
                                <div class="sign-nip">NIP. {{ $chairman_nip }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>