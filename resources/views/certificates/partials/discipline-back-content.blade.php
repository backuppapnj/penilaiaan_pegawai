{{-- Back page content for Discipline certificate --}}
<div style="page-break-before: always; position: relative; width: 100%; height: 100%; background-color: #ffffff;"></div>
<style>
    .disc-back-page-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 15mm;
        box-sizing: border-box;
        background-color: #ffffff;
        z-index: 9999;
    }

    .disc-back-border-frame {
        width: 100%;
        height: 100%;
        border: 3px solid #1a365d;
        padding: 10mm;
        box-sizing: border-box;
    }

    .disc-back-content {
        text-align: center;
    }

    .disc-back-header {
        margin-bottom: 8mm;
    }

    .disc-back-header h1 {
        color: #1a365d;
        font-size: 18pt;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 3mm 0;
        font-weight: 900;
    }

    .disc-back-header h2 {
        color: #b79c5a;
        font-size: 16pt;
        text-transform: uppercase;
        margin: 0;
        font-weight: normal;
        letter-spacing: 2px;
    }

    .disc-back-recipient-info {
        margin-bottom: 8mm;
        padding-bottom: 5mm;
        border-bottom: 1px solid #e2e8f0;
    }

    .disc-back-recipient-name {
        font-size: 18pt;
        color: #1a365d;
        font-weight: bold;
        text-transform: uppercase;
        margin: 0 0 2mm 0;
    }

    .disc-back-recipient-detail {
        font-size: 11pt;
        color: #4a5568;
    }

    .disc-back-section-title {
        font-size: 14pt;
        color: #1a365d;
        font-weight: bold;
        margin: 6mm 0 4mm 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .disc-back-score-table {
        width: 80%;
        margin: 0 auto;
        border-collapse: collapse;
        font-size: 12pt;
    }

    .disc-back-score-table th {
        background-color: #1a365d;
        color: white;
        padding: 3mm 5mm;
        text-align: left;
        font-weight: bold;
    }

    .disc-back-score-table th:last-child {
        text-align: center;
        width: 20%;
    }

    .disc-back-score-table td {
        padding: 3mm 5mm;
        border-bottom: 1px solid #e2e8f0;
    }

    .disc-back-score-table td:last-child {
        text-align: center;
        font-weight: bold;
        color: #1a365d;
    }

    .disc-back-score-table tr:nth-child(even) {
        background-color: #f7fafc;
    }

    .disc-back-score-table .total-row {
        background-color: #1a365d;
        color: white;
    }

    .disc-back-score-table .total-row td {
        color: white;
        font-size: 14pt;
        padding: 4mm 5mm;
        border-top: 2px solid #1a365d;
    }

    .disc-back-attendance-stats {
        margin-top: 6mm;
        text-align: center;
    }

    .disc-back-stat-box {
        display: inline-block;
        background-color: #f7fafc;
        border: 1px solid #e2e8f0;
        padding: 4mm 8mm;
        border-radius: 3mm;
        text-align: center;
        min-width: 40mm;
        margin: 0 4mm;
    }

    .disc-back-stat-box .value {
        font-size: 16pt;
        font-weight: bold;
        color: #1a365d;
        margin-bottom: 1mm;
    }

    .disc-back-stat-box .label {
        font-size: 9pt;
        color: #718096;
    }

    .disc-back-footer-note {
        margin-top: 6mm;
        font-size: 10pt;
        color: #718096;
        font-style: italic;
    }

    .disc-back-period-info {
        margin-top: 5mm;
        font-size: 11pt;
        color: #4a5568;
    }

    .disc-back-stats-container {
        margin-top: 5mm;
    }
</style>
<div class="disc-back-page-container">
    <div class="disc-back-border-frame">
        <div class="disc-back-content">
            <div class="disc-back-header">
                <h1>{{ $institution_name }}</h1>
                <h2>Detail Penilaian Kedisiplinan</h2>
            </div>

            <div class="disc-back-recipient-info">
                <div class="disc-back-recipient-name">{{ $employee->nama }}</div>
                <div class="disc-back-recipient-detail">
                    NIP. {{ $employee->nip }} | {{ $employee->jabatan }}
                </div>
            </div>

            <div class="disc-back-section-title">Rekapitulasi Skor Kedisiplinan</div>

            <table class="disc-back-score-table">
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

            <div class="disc-back-stats-container">
                <div class="disc-back-section-title">Statistik Kehadiran</div>
                <div class="disc-back-attendance-stats">
                    <div class="disc-back-stat-box">
                        <div class="value">{{ $disciplineData['present_on_time'] }}</div>
                        <div class="label">Datang Tepat Waktu</div>
                    </div>
                    <div class="disc-back-stat-box">
                        <div class="value">{{ $disciplineData['leave_on_time'] }}</div>
                        <div class="label">Pulang Tepat Waktu</div>
                    </div>
                    <div class="disc-back-stat-box">
                        <div class="value">{{ $disciplineData['total_work_days'] }}</div>
                        <div class="label">Total Hari Kerja</div>
                    </div>
                    <div class="disc-back-stat-box">
                        <div class="value">{{ $disciplineData['late_minutes'] }}</div>
                        <div class="label">Menit Terlambat</div>
                    </div>
                </div>
            </div>

            <div class="disc-back-period-info">
                Periode: <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
            </div>

            <div class="disc-back-footer-note">
                * Skor dihitung berdasarkan data kehadiran yang tercatat dalam sistem
            </div>
        </div>
    </div>
</div>
