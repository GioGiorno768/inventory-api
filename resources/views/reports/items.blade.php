<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .summary {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .low-stock {
            color: red;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SMART INVENTORY MANAGEMENT SYSTEM</h2>
        <h3>{{ $title }}</h3>
    </div>

    <div class="info">
        <strong>Tanggal Cetak:</strong> {{ $date }}
    </div>

    <div class="summary">
        <div class="summary-item">
            <strong>Total Jenis Barang:</strong> {{ $total_items }}
        </div>
        <div class="summary-item">
            <strong>Total Stok:</strong> {{ $total_stock }}
        </div>
        <div class="summary-item">
            <strong>Barang Stok Rendah:</strong> <span class="low-stock">{{ $low_stock_count }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>Batas Minimum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category }}</td>
                <td class="{{ $item->stock <= $item->threshold ? 'low-stock' : '' }}">
                    {{ $item->stock }}
                </td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->threshold }}</td>
                <td>
                    @if($item->stock <= $item->threshold)
                        <span class="low-stock">Stok Rendah</span>
                    @else
                        Normal
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ $date }} oleh {{ auth()->user()->name }}</p>
    </div>
</body>
</html>