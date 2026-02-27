<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Electronic Receipt</title>

<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #edf2f7;
    margin: 0;
    padding: 28px;
    color: #0f172a;
}

.receipt {
    max-width: 700px;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.top-accent {
    height: 6px;
    background: #991b1b;
}

.content {
    padding: 24px 28px;
}

/* HEADER */
.header {
    width: 100%;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 16px;
    margin-bottom: 18px;
}

.header-table {
    width: 100%;
}

.brand {
    font-size: 20px;
    font-weight: 700;
}

.brand-subtitle {
    font-size: 12px;
    color: #475569;
    margin-top: 2px;
}

.tx-block {
    text-align: right;
}

.doc-label {
    display: inline-block;
    font-size: 10px;
    text-transform: uppercase;
    color: #0f3d7a;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: 3px 10px;
    font-weight: 600;
    margin-bottom: 6px;
}

.tx-id {
    font-family: Consolas, monospace;
    font-size: 14px;
    font-weight: 700;
}

.tx-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
}

.status {
    display: inline-block;
    margin-top: 6px;
    font-size: 10px;
    font-weight: 700;
    color: #166534;
    background: #dcfce7;
    border: 1px solid #86efac;
    border-radius: 999px;
    padding: 3px 9px;
}

/* SECTION */
.section {
    margin-top: 20px;
}

.section-title {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: #475569;
    margin-bottom: 10px;
}

/* STUDENT INFO */
.info-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.info-table td {
    padding: 8px 12px;
    vertical-align: top;
}

.info-label {
    width: 120px;
    color: #64748b;
}

.info-value {
    font-weight: 600;
}

/* BREAKDOWN */
.breakdown {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 16px 14px;
}

.breakdown table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.breakdown th {
    text-align: left;
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
}

.breakdown td {
    padding: 11px 0;
    border-bottom: 1px solid #f1f5f9;
}

.breakdown tbody tr:last-child td {
    border-bottom: none;
}

.amount {
    text-align: right;
    font-weight: 600;
}

/* SUMMARY */
.summary {
    margin-top: 14px;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
    font-size: 12px;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
}

.summary-table td {
    padding: 6px 0;
}

.summary-label {
    color: #475569;
}

.summary-value {
    text-align: right;
    font-weight: 600;
}

.summary-total td {
    border-top: 1px solid #cbd5e1;
    padding-top: 10px;
    font-size: 15px;
    font-weight: 700;
}

.summary-total .summary-value {
    color: #0f3d7a;
}

/* FOOTER */
.footer {
    margin-top: 24px;
    text-align: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 12px;
    color: #64748b;
    font-size: 10px;
}
</style>
</head>

<body>

<div class="receipt">
    <div class="top-accent"></div>

    <div class="content">

        <div class="header">
            <table class="header-table">
                <tr>
                    <td>
                        <div class="brand">WMSU PayNet</div>
                        <div class="brand-subtitle">
                            Western Mindanao State University Payment Network
                        </div>
                    </td>
                    <td class="tx-block">
                        <div class="doc-label">Official Receipt</div><br>
                        <div class="tx-id">{{ $payment->transaction_id }}</div>
                        <div class="tx-meta">
                            {{ $payment->created_at->format('M d, Y h:i A') }}
                        </div>
                        <div class="status">PAID</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Student Information</div>

            <div class="info-card">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Name</td>
                        <td class="info-value">
                            {{ $payment->student->last_name }},
                            {{ $payment->student->first_name }}
                            {{ $payment->student->middle_name }}
                        </td>
                        <td class="info-label">Student ID</td>
                        <td class="info-value">
                            {{ $payment->student->student_id }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Organization</td>
                        <td class="info-value">
                            {{ $payment->organization->name ?? '-' }}
                        </td>
                        <td class="info-label">Collected By</td>
                        <td class="info-value">
                            {{ $payment->collector->full_name ?? '-' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Payment Breakdown</div>

            <div class="breakdown">
                <table>
                    <thead>
                        <tr>
                            <th>Fee</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->fees as $fee)
                        <tr>
                            <td>{{ $fee->fee_name }}</td>
                            <td class="amount">
                                ₱{{ number_format($fee->pivot->amount_paid ?? $fee->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary">
                    <table class="summary-table">
                        <tr class="summary-total">
                            <td class="summary-label">Total Paid</td>
                            <td class="summary-value">
                                ₱{{ number_format($payment->amount_due, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="summary-label">Cash Received</td>
                            <td class="summary-value">
                                ₱{{ number_format($payment->cash_received, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="summary-label">Change</td>
                            <td class="summary-value">
                                ₱{{ number_format($payment->change, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">
            System-generated electronic receipt.<br>
            No signature required.
        </div>

    </div>
</div>

</body>
</html>