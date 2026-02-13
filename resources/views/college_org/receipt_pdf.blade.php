<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .section {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        table th {
            background: #f2f2f2;
        }

        .total-row td {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="title">OFFICIAL PAYMENT RECEIPT</div>
    <div>{{ auth()->user()->organization->name }}</div>
</div>

<div class="section">
    <div><span class="label">Transaction ID:</span> {{ $payment->transaction_id }}</div>
    <div><span class="label">Date:</span> {{ $payment->created_at->format('F d, Y h:i A') }}</div>
</div>

<div class="section">
    <div><span class="label">Student ID:</span> {{ $payment->student->student_id }}</div>
    <div><span class="label">Student Name:</span> {{ $payment->student->last_name }}, {{ $payment->student->first_name }}</div>
    <div><span class="label">Course:</span> {{ $payment->enrollment->course->name ?? '-' }}</div>
    <div><span class="label">Year & Section:</span>
        {{ $payment->enrollment->yearLevel->name ?? '-' }}
        -
        {{ $payment->enrollment->section->name ?? '-' }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Fee Name</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payment->fees as $fee)
        <tr>
            <td>{{ $fee->fee_name }}</td>
            <td class="right">₱ {{ number_format($fee->pivot->amount, 2) }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td>Total</td>
            <td class="right">₱ {{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr>
            <td>Cash Received</td>
            <td class="right">₱ {{ number_format($payment->cash_received, 2) }}</td>
        </tr>
        <tr>
            <td>Change</td>
            <td class="right">₱ {{ number_format($payment->change, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="section">
    <div><span class="label">Collected By:</span> {{ $payment->collector->name ?? 'N/A' }}</div>
</div>

<div class="footer">
    This receipt serves as proof of payment.<br>
    Thank you.
</div>

</body>
</html>