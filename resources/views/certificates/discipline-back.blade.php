<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kedisiplinan - Detail Nilai</title>
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
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        .page-container {
            width: 100%;
            height: 100%;
            padding: 15mm;
            box-sizing: border-box;
        }

        .border-frame {
            width: 100%;
            height: 100%;
            border: 3px solid #1a365d;
            padding: 10mm;
            box-sizing: border-box;
        }

        .content {
            text-align: center;
        }

        .header {
            margin-bottom: 8mm;
        }

        .header h1 {
            color: #1a365d;
            font-size: 18pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 3mm 0;
            font-weight: 900;
        }

        .header h2 {
            color: #b79c5a;
            font-size: 16pt;
            text-transform: uppercase;
            margin: 0;
            font-weight: normal;
            letter-spacing: 2px;
        }

        .recipient-info {
            margin-bottom: 8mm;
            padding-bottom: 5mm;
            border-bottom: 1px solid #e2e8f0;
        }

        .recipient-name {
            font-size: 18pt;
            color: #1a365d;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 2mm 0;
        }

        .recipient-detail {
            font-size: 11pt;
            color: #4a5568;
        }

        .section-title {
            font-size: 14pt;
            color: #1a365d;
            font-weight: bold;
            margin: 6mm 0 4mm 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .score-table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 12pt;
        }

        .score-table th {
            background-color: #1a365d;
            color: white;
            padding: 3mm 5mm;
            text-align: left;
            font-weight: bold;
        }

        .score-table th:last-child {
            text-align: center;
            width: 20%;
        }

        .score-table td {
            padding: 3mm 5mm;
            border-bottom: 1px solid #e2e8f0;
        }

        .score-table td:last-child {
            text-align: center;
            font-weight: bold;
            color: #1a365d;
        }

        .score-table tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .score-table .total-row {
            background-color: #1a365d;
            color: white;
        }

        .score-table .total-row td {
            color: white;
            font-size: 14pt;
            padding: 4mm 5mm;
            border-top: 2px solid #1a365d;
        }

        .attendance-stats {
            margin-top: 6mm;
            display: flex;
            justify-content: center;
            gap: 8mm;
        }

        .stat-box {
            display: inline-block;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 4mm 8mm;
            border-radius: 3mm;
            text-align: center;
            min-width: 40mm;
        }

        .stat-box .value {
            font-size: 16pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 1mm;
        }

        .stat-box .label {
            font-size: 9pt;
            color: #718096;
        }

        .footer-note {
            margin-top: 6mm;
            font-size: 10pt;
            color: #718096;
            font-style: italic;
        }

        .period-info {
            margin-top: 5mm;
            font-size: 11pt;
            color: #4a5568;
        }

        .stats-container {
            margin-top: 5mm;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="border-frame">
            <div class="content">
                <div class="header">
                    <h1>{{ $institution_name }}</h1>
                    <h2>Detail Penilaian Kedisiplinan</h2>
                </div>

                <div class="recipient-info">
                    <div class="recipient-name">{{ $employee->nama }}</div>
                    <div class="recipient-detail">
                        NIP. {{ $employee->nip }} | {{ $employee->jabatan }}
                    </div>
                </div>

                <div class="section-title">Rekapitulasi Skor Kedisiplinan</div>

                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Komponen Penilaian</th>
                            <th>Bobot</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tingkat Kehadiran & Ketepatan Waktu</td>
                            <td style="text-align: center;">50%</td>
                            <td>{{ number_format($disciplineData['score_1'], 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Kedisiplinan (Tidak Terlambat & Pulang Awal)</td>
                            <td style="text-align: center;">35%</td>
                            <td>{{ number_format($disciplineData['score_2'], 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ketaatan (Tidak Izin Berlebih)</td>
                            <td style="text-align: center;">15%</td>
                            <td>{{ number_format($disciplineData['score_3'], 2, ',', '.') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2">Total Skor Disiplin</td>
                            <td>{{ number_format($disciplineData['final_score'], 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="stats-container">
                    <div class="section-title">Statistik Kehadiran</div>
                    <div class="attendance-stats">
                        <div class="stat-box">
                            <div class="value">{{ $disciplineData['present_on_time'] }}</div>
                            <div class="label">Datang Tepat Waktu</div>
                        </div>
                        <div class="stat-box">
                            <div class="value">{{ $disciplineData['leave_on_time'] }}</div>
                            <div class="label">Pulang Tepat Waktu</div>
                        </div>
                        <div class="stat-box">
                            <div class="value">{{ $disciplineData['total_work_days'] }}</div>
                            <div class="label">Total Hari Kerja</div>
                        </div>
                        <div class="stat-box">
                            <div class="value">{{ $disciplineData['late_minutes'] }}</div>
                            <div class="label">Menit Terlambat</div>
                        </div>
                    </div>
                </div>

                <div class="period-info">
                    Periode: <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
                </div>

                <div class="footer-note">
                    * Skor dihitung berdasarkan data kehadiran yang tercatat dalam sistem
                </div>
            </div>
        </div>
    </div>
</body>
</html>
