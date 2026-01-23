{{-- Back page content for Best Employee certificate --}}
<style>
    .back-page-container {
        page-break-before: always;
        position: relative;
        width: 297mm;
        height: 210mm;
        margin: 0;
        padding: 0;
        background-color: #ffffff;
    }

    .back-border-frame {
        position: absolute;
        top: 15mm;
        left: 18.5mm; /* (297 - 260) / 2 */
        width: 260mm;
        height: 180mm;
        border: 3px solid #1a365d;
        box-sizing: border-box;
        background-color: white;
    }

    .back-content {
        padding-top: 15mm; /* Reduced from 40mm to accommodate taller table */
        text-align: center;
        width: 100%;
    }

    .back-section-title {
        font-size: 16pt;
        color: #1a365d;
        font-weight: bold;
        margin: 0 0 10mm 0;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .back-score-table {
        width: 80%;
        margin: 0 auto;
        border-collapse: collapse;
        font-size: 12pt;
    }

    .back-score-table th {
        background-color: #1a365d;
        color: white;
        padding: 3mm 5mm;
        text-align: left;
        font-weight: bold;
    }

    .back-score-table td {
        padding: 3mm 5mm;
        border-bottom: 1px solid #e2e8f0;
    }

    .back-score-table .total-row {
        background-color: #edf2f7;
        font-weight: bold;
    }

    .back-score-table .average-row {
        background-color: #1a365d;
        color: white;
        font-weight: bold;
    }

    .back-score-table .average-row td {
        color: white;
        padding: 4mm 5mm;
    }

    .back-footer-note {
        margin-top: 10mm;
        font-size: 10pt;
        color: #718096;
        font-style: italic;
    }

    .back-period-info {
        margin-top: 8mm;
        font-size: 12pt;
        color: #4a5568;
    }
</style>
<div class="back-page-container">
    <div class="back-border-frame">
        <div class="back-content">
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
                        <th style="text-align: center;">Rata-rata Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($criteriaScores as $criteria)
                    <tr>
                        <td>{{ $criteria['nama'] }}</td>
                        <td style="text-align: center;">{{ number_format($criteria['average'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Rata-rata</td>
                        <td style="text-align: center;">{{ number_format($totalScore, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="average-row">
                        <td>Rata-rata Keseluruhan (Total / {{ $criteriaCount }} Kriteria)</td>
                        <td style="text-align: center;">{{ number_format($overallAverage, 2, ',', '.') }}</td>
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