<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: sans-serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .details, .fees { width: 100%; margin-bottom: 15px; }
        .fees table { width: 100%; border-collapse: collapse; }
        .fees th, .fees td { border: 1px solid #000; padding: 5px; text-align: left; }
        .total { text-align: right; margin-top: 10px; }
        .footer { margin-top: 20px; font-size: 10pt; text-align: center; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h2>COLLEGE NAME</h2>
        <p>Digital Payment Receipt</p>
        <p>Transaction ID: {{ $payment->id }}</p>
        <p>Date: {{ $payment->created_at->format('M d, Y H:i') }}</p>
    </div>

    <div class="details">
        <h3>Student Information</h3>
        <p>ID: {{ $payment->student->student_id }}</p>
        <p>Name: {{ $payment->student->first_name }} {{ $payment->student->last_name }}</p>
        <p>Email: {{ $payment->student->email ?? '—' }}</p>
    </div>

    <div class="fees">
        <h3>Fees Paid</h3>
        <table>
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th>Amount (₱)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->fees as $fee)
                    <tr>
                        <td>{{ $fee->fee_name }}</td>
                        <td>{{ number_format($fee->pivot->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total">
        <p>Total Amount: ₱ {{ number_format($payment->amount, 2) }}</p>
        <p>Cash Received: ₱ {{ number_format($payment->cash_received, 2) }}</p>
        <p>Change: ₱ {{ number_format($payment->change, 2) }}</p>
    </div>

    <div class="footer">
        <p>Collected by: {{ $payment->collector->full_name }}</p>
        <p>Thank you for your payment!</p>
    </div>
</body>
</html>