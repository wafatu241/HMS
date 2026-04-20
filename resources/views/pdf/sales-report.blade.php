<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1, h2 {
            margin-bottom: 8px;
        }

        .summary-box {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h1>Hotel Tropicana Sales Report</h1>

    <p>
        <strong>Date From:</strong> {{ $date_from ?: 'N/A' }}<br>
        <strong>Date To:</strong> {{ $date_to ?: 'N/A' }}
    </p>

    <div class="summary-box">
        <h2>Summary</h2>
        <p><strong>Total Sales:</strong> ₱{{ number_format($total_sales, 2) }}</p>
        <p><strong>Downpayments Collected:</strong> ₱{{ number_format($total_downpayments, 2) }}</p>
        <p><strong>Unpaid Remaining Balance:</strong> ₱{{ number_format($total_remaining_balance, 2) }}</p>
        <p><strong>Paid Bookings:</strong> {{ $paid_bookings }}</p>
    </div>

    <h2>Detailed Sales</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Type</th>
                <th>Total</th>
                <th>Downpayment</th>
                <th>Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $booking)
                <tr>
                    <td>{{ $booking->created_at?->format('Y-m-d') }}</td>
                    <td>{{ $booking->user->name ?? '' }}</td>
                    <td>{{ $booking->room->room_number ?? '' }}</td>
                    <td>{{ $booking->room->roomType->name ?? '' }}</td>
                    <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                    <td>₱{{ number_format($booking->downpayment_amount, 2) }}</td>
                    <td>₱{{ number_format($booking->remaining_balance, 2) }}</td>
                    <td>{{ strtoupper($booking->payment_status ?? 'unpaid') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No sales found for this date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>