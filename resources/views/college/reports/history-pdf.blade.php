<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>History Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .summary-box {
            width: 23%;
            display: inline-block;
            border: 1px solid #ddd;
            padding: 8px;
            margin: 4px 1%;
            text-align: center;
            vertical-align: top;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 15px;
        }

        th {
            text-align: left;
            padding: 8px 6px;
            background: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            border-bottom: 2px solid #ddd;
        }

        tbody tr {
            background: #ffffff;
        }

        tbody td {
            padding: 10px 8px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        tbody tr td:first-child {
            border-left: 1px solid #e5e7eb;
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }

        tbody tr td:last-child {
            border-right: 1px solid #e5e7eb;
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        tbody tr {
            background: #fafafa;
        }

        .badge {
            padding: 3px 8px;
            font-size: 9px;
            border-radius: 12px;
            display: inline-block;
            font-weight: bold;
        }

        .paid {
            background: #d1fae5;
            color: #065f46;
        }

        .unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .logo {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle;
            margin-right: 4px;
        }

        .fee-org {
            font-size: 9px;
            color: #555;
        }

    </style>
</head>
<body>

    <h2>{{ strtoupper($tab) }} REPORT</h2>

    <p>
        Academic Year:
        {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_start)->year }}
        -
        {{ \Carbon\Carbon::parse($selectedSchoolYear->sy_end)->year }}
        | {{ ucfirst($selectedSem) }} Semester
    </p>

    <hr>

    @if($tab === 'enrollments')

    @php
    $totalStudents = $data->count();
    $assessed = $data->whereNotNull('assessed_at')->count();
    $validated = $data->whereNotNull('validated_at')->count();
    $pending = $data->whereNotNull('advised_at')->count();
    $notEnrolled = $data->whereNull('assessed_at')
    ->whereNull('validated_at')
    ->whereNull('advised_at')
    ->count();
    @endphp

    <div>
        <div class="summary-box">
            <strong>Total Students</strong><br>
            {{ $totalStudents }}
        </div>

        <div class="summary-box">
            <strong>Assessment Completed</strong><br>
            {{ $assessed }}
        </div>

        <div class="summary-box">
            <strong>Payment Completed</strong><br>
            {{ $validated }}
        </div>

        <div class="summary-box">
            <strong>Pending Payment</strong><br>
            {{ $pending }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Course</th>
                <th>Year & Section</th>
                <th>Adviser</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    {{ strtoupper($student->student->last_name) }},
                    {{ strtoupper($student->student->first_name) }}
                    <br>
                    <small>ID: {{ $student->student->student_id }}</small>
                </td>
                <td>{{ $student->course?->name ?? '—' }}</td>
                <td>
                    {{ $student->yearLevel?->name ?? '—' }}
                    {{ $student->section?->name ?? '' }}
                </td>
                <td>
                    {{ $student->adviser?->first_name ?? '—' }}
                    {{ $student->adviser?->last_name ?? '' }}
                </td>
                <td>
                    @if($student->assessed_at)
                    Assessment Completed
                    @elseif($student->validated_at)
                    For Assessment
                    @elseif($student->advised_at)
                    Pending Payment
                    @else
                    Not Enrolled
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @endif


    {{-- ========================= --}}
    {{-- PAYMENT REPORT --}}
    {{-- ========================= --}}
    @if($tab === 'payments')

    @php
    $paidFees = $data->filter(fn($p) => $p->fees[0]->pivot->amount_paid > 0);
    $unpaidFees = $data->filter(fn($p) => $p->fees[0]->pivot->amount_paid == 0);

    $totalPayments = $paidFees->count();
    $totalUnpaid = $unpaidFees->count();
    $totalAmount = $paidFees->sum(fn($p) => $p->fees[0]->pivot->amount_paid);
    @endphp

    <div>
        <div class="summary-box">
            <strong>Total Payments</strong><br>
            {{ $totalPayments }}
            <br>
            <small>Unpaid: {{ $totalUnpaid }}</small>
        </div>

        <div class="summary-box">
            <strong>Total Amount Collected</strong><br>
            ₱ {{ number_format($totalAmount, 2) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Fee Name</th>
                <th>Requirement</th>
                <th>Status</th>
                <th>Amount Paid</th>
                <th>Date Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $payment)
            @php
            $fee = $payment->fees[0];
            $amount = $fee->pivot->amount_paid;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong style="font-size:11px;">
                        {{ strtoupper($payment->student->last_name) }},
                        {{ strtoupper($payment->student->first_name) }}
                    </strong>
                    <br>
                    <span style="font-size:9px; color:#6b7280;">
                        ID: {{ $payment->student->student_id ?? '—' }}
                    </span>
                </td>
                <td>
                    @php
                    $organization = $fee->organization ?? null;
                    $college = auth()->user()->college;
                    @endphp

                    @if($organization)
                    @if($organization->logo)
                    <img src="{{ public_path('storage/'.$organization->logo) }}" class="logo">
                    @endif
                    <strong>{{ $organization->name }}</strong>
                    @else
                    @if($college && $college->logo)
                    <img src="{{ public_path('storage/'.$college->logo) }}" class="logo">
                    @endif
                    <strong>{{ $college->name ?? 'College' }}</strong>
                    @endif

                    <br>

                    <span class="fee-org">
                        {{ $fee->fee_name }}
                    </span>
                </td>
                <td>{{ ucfirst($fee->requirement_level) }}</td>
                <td>
                    @if($amount > 0)
                    <span class="badge paid">Paid</span>
                    @else
                    <span class="badge unpaid">Unpaid</span>
                    @endif
                </td>
                <td>
                    {{ $amount > 0 ? '₱ '.number_format($amount,2) : '—' }}
                </td>
                <td>
                    {{ $payment->created_at ? $payment->created_at->format('F d, Y H:i') : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @endif

</body>
</html>
