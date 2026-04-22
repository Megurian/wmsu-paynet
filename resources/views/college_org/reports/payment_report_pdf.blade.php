<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            height: 60px;
            margin-bottom: 5px;
        }

        .org-name {
            font-size: 16px;
            font-weight: bold;
        }

        .report-title {
            font-size: 13px;
            margin-top: 5px;
        }

        .meta {
            margin-top: 5px;
            font-size: 10px;
            color: #666;
        }

        .filter-box {
            margin: 15px 0;
            padding: 8px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #888;
        }

    </style>
</head>

<body>

    <div class="header">

        @if(optional(auth()->user()->organization)->logo)
        <img src="{{ public_path('storage/' . auth()->user()->organization->logo) }}" class="logo">
        @endif

        <div class="org-name">
            {{ optional(auth()->user()->organization)->name ?? 'Organization Name' }}
        </div>

        <div class="report-title">
            Payment Collection Report
        </div>

        <div class="meta">
            Generated on {{ now()->format('F d, Y h:i A') }}
        </div>

    </div>


    <div class="filter-box">
        <strong>Filters Applied:</strong><br>

        Fee: {{ request('fee_id') ?? 'All' }} |
        Date From: {{ request('date_from') ?? 'N/A' }} |
        Date To: {{ request('date_to') ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Fee Name</th>
                <th>Date Paid</th>
                <th>Collected by</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp

            @forelse($studentsWithPayments as $index => $item)
            @php
            $student = $item['student'];
            $enrollment = $item['enrollment'];
            $fee = $item['fee'];
            @endphp

            @php
            $amount = $item['amount'] ?? 0;
            $total += $amount;
            @endphp

            <tr>
                <td>{{ $index + 1 }}</td>

                <td>{{ $student->student_id ?? '-' }}</td>

                <td>
                    {{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}
                </td>

                <td>
                    {{ $fee->fee_name }}
                    @if($item['status'] === 'Pending')
                    <small>(Pending)</small>
                    @endif
                </td>

                <td>
                    {{ $item['payment_date'] ? \Carbon\Carbon::parse($item['payment_date'])->format('Y-m-d') : '-' }}
                </td>

                <td class="px-6 py-4"> {{ $item['collector']->first_name ?? 'NO PAYMENT COLLECTED' }} {{ $item['collector']->middle_name ?? '' }} {{ $item['collector']->last_name ?? '' }} </td>

                <td class="text-right">
                    {{ number_format($amount, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">
                    No payment records found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>


    <div class="summary">
        Total Collected: ₱ {{ number_format($total, 2) }}
    </div>


    <div class="footer">
        This is a system-generated report.
    </div>

</body>
</html>
