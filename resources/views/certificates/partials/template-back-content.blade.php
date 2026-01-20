{{-- Back page content for Best Employee certificate --}}
<div style="page-break-before: always; position: relative; width: 100%; height: 100%; background-color: #ffffff;"></div>
<style>
    .back-page-container {
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

    .back-border-frame {
        width: 100%;
        height: 100%;
        border: 3px solid #1a365d;
        padding: 8mm 15mm;
        box-sizing: border-box;
        display: table;
    }

    .back-content {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }

    .back-header {
        margin-bottom: 10mm;
    }

    .back-header h1 {
        color: #1a365d;
        font-size: 22pt;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0 0 4mm 0;
        font-weight: 900;
    }

    .back-header h2 {
        color: #b79c5a;
        font-size: 18pt;
        text-transform: uppercase;
        margin: 0;
        font-weight: normal;
        letter-spacing: 2px;
    }

    .back-recipient-info {
        margin-bottom: 10mm;
        padding-bottom: 6mm;
        border-bottom: 2px solid #e2e8f0;
    }

    .back-recipient-name {
        font-size: 22pt;
        color: #1a365d;
        font-weight: bold;
        text-transform: uppercase;
        margin: 0 0 3mm 0;
    }

    .back-recipient-detail {
        font-size: 13pt;
        color: #4a5568;
    }

    .back-section-title {
        font-size: 16pt;
        color: #1a365d;
        font-weight: bold;
        margin: 8mm 0 6mm 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .back-score-table {
        width: 85%;
        margin: 0 auto;
        border-collapse: collapse;
        font-size: 13pt;
    }

    .back-score-table th {
        background-color: #1a365d;
        color: white;
        padding: 4mm 6mm;
        text-align: left;
        font-weight: bold;
        font-size: 14pt;
    }

    .back-score-table th:last-child {
        text-align: center;
        width: 25%;
    }

    .back-score-table td {
        padding: 4mm 6mm;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13pt;
    }

    .back-score-table td:last-child {
        text-align: center;
        font-weight: bold;
        color: #1a365d;
        font-size: 14pt;
    }

    .back-score-table tr:nth-child(even) {
        background-color: #f7fafc;
    }

    .back-score-table .total-row {
        background-color: #edf2f7;
        font-weight: bold;
    }

    .back-score-table .total-row td {
        border-top: 2px solid #1a365d;
        font-size: 14pt;
    }

    .back-score-table .average-row {
        background-color: #1a365d;
        color: white;
    }

    .back-score-table .average-row td {
        color: white;
        font-size: 15pt;
        padding: 5mm 6mm;
        font-weight: bold;
    }

    .back-footer-note {
        margin-top: 10mm;
        font-size: 11pt;
        color: #718096;
        font-style: italic;
    }

    .back-period-info {
        margin-top: 8mm;
        font-size: 13pt;
        color: #4a5568;
    }
</style>
<div class="back-page-container">
    <div class="back-border-frame">
        <div class="back-content">
            <div class="back-header">
                <h1>{{ $institution_name }}</h1>
                <h2>Detail Penilaian Pegawai Terbaik</h2>
            </div>

            <div class="back-recipient-info">
                <div class="back-recipient-name">{{ $employee->nama }}</div>
                <div class="back-recipient-detail">
                    NIP. {{ $employee->nip }} | {{ $employee->jabatan }}
                </div>
            </div>

            <div class="back-section-title">Rekapitulasi Nilai per Kriteria</div>

            @php
                $totalScore = 0;
                $criteriaCount = count($criteriaScores);
                foreach ($criteriaScores as $criteria) {
                    $totalScore += $criteria['average'];
                }
                $overallAverage = $criteriaCount > 0 ? $totalScore / $criteriaCount : 0;
            @endphp

            <table class="back-score-table">
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
                    <tr class="total-row">
                        <td>Total Rata-rata</td>
                        <td>{{ number_format($totalScore, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="average-row">
                        <td>Rata-rata Keseluruhan (Total / {{ $criteriaCount }} Kriteria)</td>
                        <td>{{ number_format($overallAverage, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="back-period-info">
                Periode: <strong>{{ $period->name }} ({{ ucfirst($period->semester) }} {{ $period->year }})</strong>
            </div>

            <div class="back-footer-note">
                * Nilai rata-rata dihitung dari seluruh penilaian yang diberikan oleh penilai pada periode tersebut
            </div>
        </div>
    </div>
</div>
