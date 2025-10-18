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
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
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
            background-color: #2196F3;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .type-in {
            color: green;
            font-weight: bold;
        }
        .type-out {
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
        <strong>Periode:</strong> {{ $period }}<br>
        <strong>Tanggal Cetak:</strong> {{ $date }}
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div><strong>Total Transaksi:</strong> {{ $total_transactions }}</div>
            <div><strong>Barang Masuk:</strong> <span class="type-in">{{ $transactions_in_count }} transaksi ({{ $total_in }} unit)</span></div>
            <div><strong>Barang Keluar:</strong> <span class="type-out">{{ $transactions_out_count }} transaksi ({{ $total_out }} unit)</span></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Petugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</td>
                <td>{{ $transaction->item->name }}</td>
                <td class="{{ $transaction->type === 'in' ? 'type-in' : 'type-out' }}">
                    {{ $transaction->type === 'in' ? 'MASUK' : 'KELUAR' }}
                </td>
                <td>{{ $transaction->quantity }} {{ $transaction->item->unit }}</td>
                <td>{{ $transaction->user->name }}</td>
                <td>{{ $transaction->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ $date }} oleh {{ auth()->user()->name }}</p>
    </div>
</body>
</html>