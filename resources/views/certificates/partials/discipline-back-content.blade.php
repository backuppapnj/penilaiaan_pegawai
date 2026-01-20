{{-- Back page content for Discipline certificate --}}
<div style="page-break-before: always; position: relative; width: 100%; height: 100%; background-color: #ffffff;"></div>
<style>
    .disc-back-page-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 12mm;
        box-sizing: border-box;
        background-color: #ffffff;
        z-index: 9999;
    }

    .disc-back-border-frame {
        width: 100%;
        height: 100%;
        border: 3px solid #1a365d;
        padding: 8mm 15mm;
        box-sizing: border-box;
        display: table;
    }

    .disc-back-content {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }

    .disc-back-header {
        margin-bottom: 10mm;
    }

    .disc-back-header h1 {
        color: #1a365d;
        font-size: 22pt;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0 0 4mm 0;
        font-weight: 900;
    }

    .disc-back-header h2 {
        color: #b79c5a;
        font-size: 18pt;
        text-transform: uppercase;
        margin: 0;
        font-weight: normal;
        letter-spacing: 2px;
    }

    .disc-back-recipient-info {
        margin-bottom: 10mm;
        padding-bottom: 6mm;
        border-bottom: 2px solid #e2e8f0;
    }

    .disc-back-recipient-name {
        font-size: 22pt;
        color: #1a365d;
        font-weight: bold;
        text-transform: uppercase;
        margin: 0 0 3mm 0;
    }

    .disc-back-recipient-detail {
        font-size: 13pt;
        color: #4a5568;
    }

    .disc-back-section-title {
        font-size: 16pt;
        color: #1a365d;
        font-weight: bold;
        margin: 8mm 0 6mm 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .disc-back-score-table {
        width: 85%;
        margin: 0 auto;
        border-collapse: collapse;
        font-size: 13pt;
    }

    .disc-back-score-table th {
        background-color: #1a365d;
        color: white;
        padding: 4mm 6mm;
        text-align: left;
        font-weight: bold;
        font-size: 14pt;
    }

    .disc-back-score-table th:nth-child(2) {
        text-align: center;
        width: 15%;
    }

    .disc-back-score-table th:last-child {
        text-align: center;
        width: 20%;
    }

    .disc-back-score-table td {
        padding: 4mm 6mm;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13pt;
    }

    .disc-back-score-table td:nth-child(2) {
        text-align: center;
    }

    .disc-back-score-table td:last-child {
        text-align: center;
        font-weight: bold;
        color: #1a365d;
        font-size: 14pt;
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
        font-size: 15pt;
        padding: 5mm 6mm;
        border-top: 2px solid #1a365d;
        font-weight: bold;
    }

    .disc-back-attendance-stats {
        margin-top: 8mm;
        text-align: center;
    }

    .disc-back-stat-box {
        display: inline-block;
        background-color: #f7fafc;
        border: 2px solid #e2e8f0;
        padding: 6mm 12mm;
        border-radius: 4mm;
        text-align: center;
        min-width: 50mm;
        margin: 0 5mm;
    }

    .disc-back-stat-box .value {
        font-size: 20pt;
        font-weight: bold;
        color: #1a365d;
        margin-bottom: 2mm;
    }

    .disc-back-stat-box .label {
        font-size: 10pt;
        color: #718096;
    }

    .disc-back-footer-note {
        margin-top: 10mm;
        font-size: 11pt;
        color: #718096;
        font-style: italic;
    }

    .disc-back-period-info {
        margin-top: 8mm;
        font-size: 13pt;
        color: #4a5568;
    }

    .disc-back-stats-container {
        margin-top: 8mm;
    }
</style>
<div class="disc-back-page-container">
    <div class="disc-back-border-frame">
        <div class="disc-back-content">
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
                        <td>50%</td>
                        <td>{{ number_format($disciplineData['score_1'], 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Kedisiplinan (Tidak Terlambat & Pulang Awal)</td>
                        <td>35%</td>
                        <td>{{ number_format($disciplineData['score_2'], 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Ketaatan (Tidak Izin Berlebih)</td>
                        <td>15%</td>
                        <td>{{ number_format($disciplineData['score_3'], 2, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2">Total Skor Disiplin</td>
                        <td>{{ number_format($disciplineData['final_score'], 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="disc-back-period-info">
                Periode: <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
            </div>

            <div class="disc-back-footer-note">
                * Skor dihitung berdasarkan data kehadiran yang tercatat dalam sistem
            </div>
        </div>
    </div>
</div>
