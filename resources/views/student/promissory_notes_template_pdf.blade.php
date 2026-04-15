<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Promissory Note</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 28px;
            color: #111827;
            background: #ffffff;
            font-size: 12px;
        }

        .sheet {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background: #7f1d1d;
            color: #fff;
            padding: 18px 22px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            opacity: 0.9;
        }

        .content {
            padding: 22px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .meta td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            width: 150px;
        }

        .value {
            font-weight: 600;
        }

        .section-title {
            margin: 18px 0 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #7f1d1d;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }

        table.items th,
        table.items td {
            border: 1px solid #e5e7eb;
            padding: 9px 10px;
        }

        table.items th {
            background: #f9fafb;
            text-align: left;
            font-size: 11px;
        }

        .right {
            text-align: right;
        }

        .summary {
            margin-top: 16px;
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            padding: 6px 0;
        }

        .summary .total td {
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        .signatures {
            margin-top: 28px;
            width: 100%;
            border-collapse: collapse;
        }

        .signatures td {
            width: 50%;
            padding-top: 34px;
            text-align: center;
        }

        .line {
            border-top: 1px solid #111827;
            margin: 0 22px 8px;
            height: 1px;
        }

        .footer {
            margin-top: 20px;
            color: #6b7280;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1>WMSU PayNet Promissory Note</h1>
            <p>Prepared for student signature and coordinator review</p>
        </div>

        <div class="content">
            <table class="meta">
                <tr>
                    <td class="label">PN ID</td>
                    <td class="value">{{ $note->id }}</td>
                    <td class="label">Status</td>
                    <td class="value">{{ $note->status }}</td>
                </tr>
                <tr>
                    <td class="label">Student</td>
                    <td class="value">{{ $student->full_name ?? trim($student->first_name . ' ' . $student->last_name) }}</td>
                    <td class="label">Student ID</td>
                    <td class="value">{{ $student->student_id }}</td>
                </tr>
                <tr>
                    <td class="label">Course / Section</td>
                    <td class="value">{{ $note->enrollment->course->name ?? '—' }} / {{ $note->enrollment->section->name ?? '—' }}</td>
                    <td class="label">Due Date</td>
                    <td class="value">{{ optional($note->due_date)->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Signature Deadline</td>
                    <td class="value">{{ optional($note->signature_deadline)->format('M d, Y h:i A') }}</td>
                    <td class="label">Issued By</td>
                    <td class="value">{{ $note->issuedBy->full_name ?? '—' }}</td>
                </tr>
            </table>

            <div class="section-title">Deferred Fees</div>

            <table class="items">
                <thead>
                    <tr>
                        <th>Fee</th>
                        <th>Scope</th>
                        <th class="right">Deferred Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($note->fees as $fee)
                        <tr>
                            <td>{{ $fee->fee_name }}</td>
                            <td>{{ $fee->organization->name ?? ($fee->college->name ?? 'College') }}</td>
                            <td class="right">₱{{ number_format((float) $fee->pivot->amount_deferred, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="summary">
                <tr>
                    <td>Total Deferred</td>
                    <td class="right">₱{{ number_format((float) $note->original_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Remaining Balance</td>
                    <td class="right">₱{{ number_format((float) $note->remaining_balance, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Student Acknowledgement</td>
                    <td class="right">Signature required below</td>
                </tr>
            </table>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="line"></div>
                        Student Signature
                    </td>
                    <td>
                        <div class="line"></div>
                        Coordinator Review
                    </td>
                </tr>
            </table>

            <div class="footer">
                This template is not active until signed by the student and approved by the coordinator.
            </div>
        </div>
    </div>
</body>
</html>