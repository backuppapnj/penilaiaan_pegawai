{{-- Back page content for Discipline certificate --}}
<style>
    .disc-back-page-container {
        page-break-before: always;
        position: relative;
        width: 297mm;
        height: 210mm;
        margin: 0;
        padding: 0;
        background-color: #ffffff;
    }

    .disc-back-border-frame {
        position: absolute;
        top: 15mm;
        left: 18.5mm;
        width: 260mm;
        height: 180mm;
        border: 3px solid #1a365d;
        box-sizing: border-box;
        background-color: white;
    }

    .disc-back-content {
        padding-top: 40mm;
        text-align: center;
        width: 100%;
    }

    .disc-back-section-title {
        font-size: 16pt;
        color: #1a365d;
        font-weight: bold;
        margin: 0 0 10mm 0;
        text-transform: uppercase;
        letter-spacing: 1.5px;
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
        padding: 4mm 6mm;
        text-align: left;
        font-weight: bold;
    }

    .disc-back-score-table td {
        padding: 4mm 6mm;
        border-bottom: 1px solid #e2e8f0;
    }

    .disc-back-score-table .total-row {
        background-color: #1a365d;
        color: white;
        font-weight: bold;
    }

    .disc-back-score-table .total-row td {
        color: white;
        font-size: 14pt;
        padding: 5mm 6mm;
    }

    .disc-back-footer-note {
        margin-top: 12mm;
        font-size: 10pt;
        color: #718096;
        font-style: italic;
    }

    .disc-back-period-info {
        margin-top: 10mm;
        font-size: 12pt;
        color: #4a5568;
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
                        <th style="text-align: center;">Bobot</th>
                        <th style="text-align: center;">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tingkat Kehadiran & Ketepatan Waktu</td>
                        <td style="text-align: center;">50%</td>
                        <td style="text-align: center;">{{ number_format($disciplineData['score_1'], 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Kedisiplinan (Tidak Terlambat & Pulang Awal)</td>
                        <td style="text-align: center;">35%</td>
                        <td style="text-align: center;">{{ number_format($disciplineData['score_2'], 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Ketaatan (Tidak Izin Berlebih)</td>
                        <td style="text-align: center;">15%</td>
                        <td style="text-align: center;">{{ number_format($disciplineData['score_3'], 2, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2">Total Skor Disiplin</td>
                        <td style="text-align: center;">{{ number_format($disciplineData['final_score'], 2, ',', '.') }}</td>
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