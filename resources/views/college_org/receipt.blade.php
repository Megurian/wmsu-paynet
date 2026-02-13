<!DOCTYPE html>
<html>
<head>
    <title>Receipt - {{ $payment->transaction_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .container {
            width: 700px;
            margin: auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .line {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
        }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h2>OFFICIAL RECEIPT</h2>
        <p>Transaction ID: <strong>{{ $payment->transaction_id }}</strong></p>
        <p>Date: {{ $payment->created_at->format('F d, Y h:i A') }}</p>
    </div>

    <div class="line"></div>

    <p><strong>Student:</strong> 
        {{ $payment->student->last_name }}, 
        {{ $payment->student->first_name }}
    </p>
    <p><strong>Student ID:</strong> {{ $payment->student->student_id }}</p>

    <p><strong>Course:</strong> {{ $payment->enrollment?->course?->name }}</p>
    <p><strong>Year & Section:</strong>
        {{ $payment->enrollment?->yearLevel?->name }} -
        {{ $payment->enrollment?->section?->name }}
    </p>

    <div class="line"></div>

    <h4>Payment Details</h4>

    <table>
        <thead>
            <tr>
                <th>Fee</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->fees as $fee)
                <tr>
                    <td>{{ $fee->fee_name }}</td>
                    <td class="text-right">
                        ₱ {{ number_format($fee->pivot->amount, 2) }}
                    </td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">
                    ₱ {{ number_format($payment->amount, 2) }}
                </td>
            </tr>

            <tr>
                <td>Cash Received</td>
                <td class="text-right">
                    ₱ {{ number_format($payment->cash_received, 2) }}
                </td>
            </tr>

            <tr>
                <td>Change</td>
                <td class="text-right">
                    ₱ {{ number_format($payment->change, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Collected By:</strong> {{ $payment->collector->name }}</p>
        <p>Signature: ___________________________</p>
    </div>

    <br><br>

    <button onclick="window.print()">Print Receipt</button>

</div>

</body>
</html>