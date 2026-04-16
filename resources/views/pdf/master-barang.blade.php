<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Form Harian Angkringan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 6pt;
            color: #000;
            padding: 4px 6px;
        }

        .top-header {
            width: 100%;
            display: table;
            margin-bottom: 3px;
        }

        .th-col {
            display: table-cell;
            width: 50%;
            font-size: 6.5pt;
            font-weight: bold;
            vertical-align: bottom;
        }

        .th-inner {
            display: table;
            width: 100%;
        }

        .th-label {
            display: table-cell;
            white-space: nowrap;
            padding-right: 2px;
        }

        .th-dots {
            display: table-cell;
            width: 100%;
            font-weight: normal;
            font-size: 6.5pt;
            vertical-align: bottom;
            padding-bottom: 1px;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 0.8px solid #000;
            padding: 0px 2px;
            font-size: 6pt;
            vertical-align: middle;
        }

        thead th {
            text-align: center;
            font-weight: bold;
            background: #fff;
            font-size: 5.5pt;
            padding: 1px 2px;
        }

        .col-no {
            width: 5%;
            text-align: center;
        }

        .col-barang {
            width: 35%;
        }

        .col-harga {
            width: 20%;
            text-align: center;
        }

        .col-bawa {
            width: 13%;
            text-align: center;
        }

        .col-sisa {
            width: 10%;
            text-align: center;
        }

        .col-jumlah {
            width: 17%;
            text-align: center;
        }

        .td-no {
            text-align: center;
            font-weight: bold;
        }

        .td-barang {
            padding-left: 3px;
        }

        .td-harga {
            padding-left: 3px;
        }

        .td-jumlah {
            padding-left: 3px;
            vertical-align: top;
            padding-top: 2px;
            font-size: 6pt;
        }

        /* Row height dikecilkan drastis */
        tbody tr td {
            height: 11px;
        }

        .td-summary-label {
            text-align: right;
            font-weight: bold;
            padding-right: 4px;
            font-size: 6pt;
        }

        .footer-note {
            margin-top: 3px;
            font-size: 5pt;
            color: #888;
            font-style: italic;
        }
    </style>
</head>

<body>

    {{-- ── TOP LABELS: NAMA (left) | HARI/TGL (also left-aligned with colon) ── --}}
    <div class="top-header">
        <div class="th-col">
            <div class="th-inner">
                <span class="th-label">NAMA</span>
                <span class="th-dots">&nbsp;: ................................</span>
            </div>
        </div>
        <div class="th-col">
            <div class="th-inner">
                <span class="th-label" style="text-align: right; justify-content:">HARI/TGL</span>
                <span class="th-dots">&nbsp;: ................................</span>
            </div>
        </div>
    </div>

    {{-- ── MAIN TABLE (6 columns) ── --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-barang">BARANG YANG DIPESAN</th>
                <th class="col-harga">HARGA<br>(KG/UNIT)</th>
                <th class="col-bawa">MEMBAWA</th>
                <th class="col-sisa">SISA</th>
                <th class="col-jumlah">JUMLAH</th>
            </tr>
        </thead>
        <tbody>

            {{-- ── ALL BARANGS from DB ordered by ID ── --}}
            @foreach ($barangs as $barang)
                <tr>
                    <td class="td-no">{{ $barang->id }}</td>
                    <td class="td-barang">{{ $barang->nama_barang }}</td>
                    <td class="td-harga">Rp &nbsp;{{ number_format($barang->harga_per_unit, 0, ',', '.') }}</td>
                    <td class="td-bawa"></td>
                    <td class="td-sisa"></td>
                    <td class="td-jumlah">Rp</td>
                </tr>
            @endforeach

            {{-- ── BLANK ROWS to pad to at least 20 rows ── --}}
            @php $filled = count($barangs); @endphp
            @for ($i = 0; $i < max(0, 20 - $filled); $i++)
                <tr>
                    <td class="td-no"></td>
                    <td class="td-barang"></td>
                    <td class="td-harga"></td>
                    <td class="td-bawa"></td>
                    <td class="td-sisa"></td>
                    <td class="td-jumlah"></td>
                </tr>
            @endfor

            {{-- ── JUMLAH summary row (MEMBAWA + SISA still have borders for writing) ── --}}
            <tr>
                <td colspan="3"></td>
                <td class="td-summary-blank"><strong>JUMLAH</strong></td> {{-- MEMBAWA col --}}
                <td class="td-summary-blank"></td> {{-- SISA col --}}
                <td class="td-summary-label"></td>
            </tr>

            {{-- ── SETOR summary row ── --}}
            <tr>
                <td colspan="3"></td>
                <td class="td-summary-blank"><strong>SETOR</strong></td> {{-- MEMBAWA col --}}
                <td class="td-summary-blank"></td> {{-- SISA col --}}
                <td class="td-summary-label"></td>
            </tr>

            <tr>
                <td colspan="3"></td>
                <td class="td-summary-blank"></td>
                <td class="td-summary-blank"></td>
                <td class="td-summary-label"></td>
            </tr>

        </tbody>
    </table>

    <div class="footer-note">
        Dicetak oleh ipangyuwono70 <br> {{ now()->format('d/m/Y H:i') }} &middot; Angkringan Asli Klaten
    </div>

</body>

</html>
