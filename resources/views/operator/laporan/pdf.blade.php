<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Prediksi Pendapatan Retribusi Parkir</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        /* Kop Surat Styles */
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo-placeholder {
            width: 60px;
            height: 60px;
            border: 2px solid #005baa;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 56px;
            font-size: 10px;
            color: #005baa;
            font-weight: bold;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-text h2 {
            margin: 0 0 2px 0;
            font-size: 15px;
            letter-spacing: 0.5px;
            color: #000000;
            font-weight: bold;
        }
        .kop-text h1 {
            margin: 0 0 2px 0;
            font-size: 18px;
            letter-spacing: 1px;
            color: #000000;
            font-weight: bold;
        }
        .kop-text h3 {
            margin: 0 0 4px 0;
            font-size: 12px;
            color: #000000;
            font-weight: normal;
        }
        .kop-text p {
            margin: 0;
            font-size: 9px;
            color: #555555;
            font-style: italic;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h4 {
            margin: 0 0 4px 0;
            font-size: 12px;
            color: #005baa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title h3 {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #111111;
            font-weight: bold;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 0;
            font-size: 10px;
            color: #666666;
        }

        /* Metadata info */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10px;
        }
        .meta-label {
            width: 120px;
            color: #666666;
        }
        .meta-value {
            font-weight: bold;
            color: #222222;
        }

        /* Metrics Grid using Table */
        .metrics-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .metrics-table td {
            width: 25%;
            padding: 8px;
            border: 1px solid #e2e8f0;
            text-align: center;
            background-color: #f8fafc;
        }
        .metric-title {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .metric-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #005baa;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 7px 8px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #005baa;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            padding: 7px 8px;
        }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* Status Badge colors */
        .badge-success {
            color: #10b981;
            font-weight: bold;
        }
        .badge-danger {
            color: #ef4444;
            font-weight: bold;
        }

        /* Signature block */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 10px;
        }
        .sig-left {
            width: 60%;
        }
        .sig-right {
            width: 40%;
            text-align: center;
        }
        .sig-space {
            height: 60px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000000;
        }
        .sig-nip {
            color: #555555;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <table class="kop-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('img/logo/dishubpng.png') }}" alt="Logo Dishub" style="height: 55px; width: auto;">
            </td>
            <td class="kop-text">
                <h2>PEMERINTAH KOTA CIREBON</h2>
                <h1>DINAS PERHUBUNGAN</h1>
                <h3>UPT PARKIR KOTA CIREBON</h3>
                <p>Jl. Kesambi No. 202 Kota Cirebon | Pos: 45134 | Email: uptparkir@cirebonkota.go.id</p>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div class="doc-title">
        <h3>LAPORAN PREDIKSI PENDAPATAN RETRIBUSI PARKIR</h3>
        <h4>METODE SUPPORT VECTOR REGRESSION (SVR) DENGAN GWO OPTIMIZATION</h4>
    </div>

    <!-- Metadata -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Periode Pemantauan</td>
            <td style="width: 10px;">:</td>
            <td class="meta-value">{{ $summary['periode'] }}</td>
            <td class="meta-label" style="text-align: right; padding-right: 10px;">Filter Wilayah</td>
            <td style="width: 10px;">:</td>
            <td class="meta-value" style="width: 150px;">{{ $rayonName }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Data Periode</td>
            <td>:</td>
            <td class="meta-value">{{ $summary['total_data'] }}</td>
            <td class="meta-label" style="text-align: right; padding-right: 10px;">Rerata Error (MAPE)</td>
            <td>:</td>
            <td class="meta-value">{{ $summary['mape'] }}</td>
        </tr>
    </table>

    <!-- Evaluasi Model Header -->
    <h5 style="margin: 15px 0 8px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; color: #1e293b;">
        Kinerja Model Evaluasi SVR
    </h5>
    <!-- Metrics Table -->
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-title">Mean Absolute Error (MAE)</div>
                <div class="metric-value">{{ $metrics['mae'] }}</div>
            </td>
            <td>
                <div class="metric-title">Root Mean Squared Error (RMSE)</div>
                <div class="metric-value">{{ $metrics['rmse'] }}</div>
            </td>
            <td>
                <div class="metric-title">Mean Absolute Percentage Error (MAPE)</div>
                <div class="metric-value" style="color: #10b981;">{{ $metrics['mape'] }}</div>
            </td>
            <td>
                <div class="metric-title">R-Squared (R² Score)</div>
                <div class="metric-value" style="color: #005baa;">{{ $metrics['r2'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table Header -->
    <h5 style="margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; color: #1e293b;">
        Rincian Transaksi {{ ucfirst($type ?? 'Harian') }} Retribusi Parkir
    </h5>
    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 100px;">Tanggal / Periode</th>
                <th>Rayon Pemantauan</th>
                <th style="text-align: right; width: 120px;">Realisasi Aktual (Rp)</th>
                <th style="text-align: right; width: 120px;">Prediksi Target SVR (Rp)</th>
                <th style="text-align: right; width: 110px;">Selisih/Error (Rp)</th>
                <th style="text-align: right; width: 70px;">% Kesalahan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $rep)
                <tr>
                    <td class="text-center">{{ $rep['no'] }}</td>
                    <td>{{ $rep['tanggal_formatted'] ?? $rep['tanggal'] }}</td>
                    <td>{{ $rep['rayon'] }}</td>
                    <td class="text-right">Rp {{ number_format($rep['aktual'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: 500; color: #005baa;">Rp {{ number_format($rep['prediksi'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: {{ $rep['error'] >= 0 ? '#10b981' : '#ef4444' }};">
                        {{ $rep['error'] >= 0 ? '+' : '' }}Rp {{ number_format($rep['error'], 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        {{ $rep['pct_error'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #777777;">
                        Tidak ada data transaksi laporan yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($reports) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="text-center">TOTAL KINERJA PERIODE INI</td>
                    <td class="text-right" style="color: #10b981;">{{ $total_period['aktual'] }}</td>
                    <td class="text-right" style="color: #005baa;">{{ $total_period['prediksi'] }}</td>
                    <td class="text-right">{{ $total_period['error'] }}</td>
                    <td class="text-right">Rerata: {{ $total_period['pct_error'] }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td class="sig-left">
                <div style="font-style: italic; color: #666666; font-size: 8px;">
                    * Laporan ini diekstrak dari Sistem Informasi Analisis Prediksi Retribusi Parkir Kota Cirebon (REVORA).<br>
                    * Dicetak pada: {{ date('d-m-Y H:i:s') }} oleh Operator UPT Parkir.
                </div>
            </td>
            <td class="sig-right">
                <div>Cirebon, {{ date('d M Y') }}</div>
                <div style="font-weight: bold; margin-top: 4px;">Kepala UPT Parkir Kota Cirebon</div>
                <div class="sig-space"></div>
                <div class="sig-name">H. AGUS DEDI HERMAWAN, S.E., M.Si.</div>
                <div class="sig-nip">NIP. 19740822 199803 1 002</div>
            </td>
        </tr>
    </table>

</body>
</html>
