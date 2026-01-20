<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Penghargaan - Detail Nilai</title>
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
            width: 25%;
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
            background-color: #edf2f7;
            font-weight: bold;
        }

        .score-table .total-row td {
            border-top: 2px solid #1a365d;
            font-size: 13pt;
        }

        .score-table .average-row {
            background-color: #1a365d;
            color: white;
        }

        .score-table .average-row td {
            color: white;
            font-size: 14pt;
            padding: 4mm 5mm;
        }

        .footer-note {
            margin-top: 8mm;
            font-size: 10pt;
            color: #718096;
            font-style: italic;
        }

        .period-info {
            margin-top: 5mm;
            font-size: 11pt;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="border-frame">
            <div class="content">
                <div class="header">
                    <h1>{{ $institution_name }}</h1>
                    <h2>Detail Penilaian Pegawai Terbaik</h2>
                </div>

                <div class="recipient-info">
                    <div class="recipient-name">{{ $employee->nama }}</div>
                    <div class="recipient-detail">
                        NIP. {{ $employee->nip }} | {{ $employee->jabatan }}
                    </div>
                </div>

                <div class="section-title">Rekapitulasi Nilai per Kriteria</div>

                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Kriteria Penilaian</th>
                            <th>Rata-rata Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criteriaScores as $criteria)
                        <tr>
                            <td>{{ $criteria['nama'] }}</td>
                            <td>{{ number_format($criteria['average'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="average-row">
                            <td>Rata-rata Keseluruhan</td>
                            <td>{{ number_format($averageScore, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="period-info">
                    Periode: <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
                </div>

                <div class="footer-note">
                    * Nilai rata-rata dihitung dari seluruh penilaian yang diberikan oleh penilai pada periode tersebut
                </div>
            </div>
        </div>
    </div>
</body>
</html>
