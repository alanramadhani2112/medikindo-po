<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen PT. Mentari Medika Indonesia')</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .header td {
            vertical-align: top;
        }
        .logo-text {
            color: #2563eb;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .company-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .doc-title {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-meta {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
            border-spacing: 0;
        }
        .info-section td {
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            background-color: #f8fafc;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-right: 10px;
            height: 100px;
        }
        .info-box:last-child {
            margin-right: 0;
            margin-left: 10px;
        }
        .info-title {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin: 0 0 8px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold !important; }
        .text-lg { font-size: 16px !important; }
        .text-blue { color: #2563eb !important; }
        
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="logo-text">PT. Mentari Medika Indonesia</h1>
                    <div class="company-info">
                        Jl. Raya Farmasi No. 123, Jakarta Selatan 12345<br>
                        Telp. (021) 1234-5678 • Email: finance@mentarimedika.co.id<br>
                        NPWP: 01.234.567.8-901.000
                    </div>
                </td>
                <td>
                    <h2 class="doc-title">@yield('document_name')</h2>
                    <div class="doc-meta">
                        Nomor: <b>@yield('document_number')</b><br>
                        Tanggal: @yield('document_date')<br>
                        Halaman: 1 / 1
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @yield('content')

    <div style="position: absolute; bottom: 20px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8;">
        Dokumen ini dibuat oleh Sistem PT. Mentari Medika Indonesia dan merupakan bukti administratif yang sah secara elektronik.
    </div>
</body>
</html>
