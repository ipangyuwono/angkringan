<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Angkringan - {{ $tanggal }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 7pt;
            color: #000;
            padding: 6px 10px 6px;
        }

        /* ── HEADER ── */
        .header-table, .header-table td {
            border: none !important;
            padding: 0 !important;
        }

        .header-table {
            margin-bottom: 8px;
            font-size: 8pt;
            font-weight: bold;
        }

        /* ── TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        th, td {
            border: 1px solid #444;
            padding: 1px 3px;
            font-size: 7pt;
            vertical-align: middle;
        }
        thead th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
            font-size: 6.5pt;
            text-transform: uppercase;
            padding: 2px 3px;
        }
        .col-no    { width: 2%;  text-align: center; }
        .col-nama  { width: 10%; }
        .col-harga { width: 6%; text-align: center; }
        .col-bawa  { width: 5%; text-align: center; }
        .col-sisa  { width: 4%; text-align: center; }
        .col-jml   { width: 6%; text-align: center; }

        .row-empty td { height: 10px; }

        /* Summary rows */
        .sum-label-cell {
            text-align: left;
            font-weight: bold;
            font-size: 7pt;
            padding: 2px 6px;
            letter-spacing: 0.3px;
        }
        .sum-value-cell {
            text-align: right;
            font-size: 7pt;
            padding: 2px 6px;
        }
        .row-jumlah { background: #f9f9f9; }
        .row-setor  { background: #fffbeb; }

        .footer-note {
            margin-top: 4px;
            font-size: 3pt;
            color: #555;
            font-style: italic;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="text-align: left; font-size: 5pt; font-weight: bold;">
                NAMA : {{ $nama !== '-' ? $nama : '' }}
            </td>
            <td style="text-align: right; font-size: 5pt; font-weight: bold;">
                HARI / TGL : {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </td>
        </tr>
    </table>

    {{-- ── TABLE ── --}}
    @php
        $transaksiMap = $transaksis->keyBy('barang_id');
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Barang yang Dipesan</th>
                    <th class="col-harga">Harga</th>
                <th class="col-bawa">Membawa</th>
                <th class="col-sisa">Sisa</th>
                <th class="col-jml">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barangs as $i => $barang)
            @php $t = $transaksiMap->get($barang->id); @endphp
            <tr>
                <td class="col-no">{{ $i + 1 }}</td>
                <td class="col-nama">{{ $barang->nama_barang }}</td>
                <td class="col-harga">Rp&nbsp;{{ number_format($barang->harga_per_unit, 0, ',', '.') }}</td>
                <td class="col-bawa">{{ $t ? (float)$t->jumlah_bawa : '' }}</td>
                <td class="col-sisa">{{ $t ? (float)$t->sisa : '' }}</td>
                <td class="col-jml">
                    {{ $t ? 'Rp ' . number_format($t->jumlah, 0, ',', '.') : '' }}
                </td>
            </tr>
            @endforeach

            {{-- Extra blank rows --}}
            @for($e = 0; $e < 2; $e++)
            <tr class="row-empty">
                <td class="col-no"></td>
                <td class="col-nama"></td>
                <td class="col-harga"></td>
                <td class="col-bawa"></td>
                <td class="col-sisa"></td>
                <td class="col-jml"></td>
            </tr>
            @endfor
        </tbody>

        <tfoot>
            {{-- JUMLAH row --}}
            <tr class="row-jumlah">
                <td colspan="3" style="border: 1px solid #444;"></td>
                <td colspan="2" class="sum-label-cell">JUMLAH</td>
                <td class="sum-value-cell">Rp {{ number_format($jumlah_total, 0, ',', '.') }}</td>
            </tr>
            {{-- SETOR row --}}
            <tr class="row-setor">
                <td colspan="3" style="border: 1px solid #444;"></td>
                <td colspan="2" class="sum-label-cell">SETOR</td>
                <td class="sum-value-cell">Rp {{ number_format($setor, 0, ',', '.') }}</td>
            </tr>
            {{-- TABUNGAN / KURANG row --}}
            @php
                $selisih = $setor - $jumlah_total;
                $isKurang = $sesi == 4 && $selisih < 0;
                $tabelLabel = $isKurang ? 'KURANG' : 'TABUNGAN';
            @endphp
            <tr class="row-setor">
                <td colspan="3" style="border: 1px solid #444;"></td>
                <td colspan="2" class="sum-label-cell" style="{{ $isKurang ? 'color: #dc2626;' : '' }}">{{ $tabelLabel }}</td>
                <td class="sum-value-cell" style="{{ $isKurang ? 'color: #dc2626;' : '' }}">Rp {{ number_format(abs($selisih), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: bottom;">
            Dicetak oleh {{ $nama !== '-' ? $nama : 'sistem' }}
            <br>
            {{ now()->format('d/m/Y H:i') }}
            &nbsp;&middot;&nbsp;
            Angkringan Asli Klaten
        </div>
        <div style="display: table-cell; text-align: right; vertical-align: bottom; font-size: 3pt; font-weight: bold;">
            TRANSAKSI {{ $sesi }}
        </div>
    </div>
</div>
</div>

</body>
</html>
