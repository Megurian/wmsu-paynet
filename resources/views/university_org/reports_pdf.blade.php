<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Collection Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h3 { margin: 0; padding: 0; }
        h2 { font-size: 16px; margin-bottom: 4px; }
        h3 { font-size: 14px; margin-top: 12px; margin-bottom: 4px; }
        p { margin: 2px 0; font-size: 12px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; }

        .org-header { margin-bottom: 8px; }
        .org-logo { width: 50px; height: 50px; object-fit: contain; border: 1px solid #ccc; margin-right: 8px; }
        .org-info { display: flex; align-items: center; margin-bottom: 12px; }

        .fee-table th, .fee-table td { font-size: 10px; }
        .sub-table { margin-left: 12px; margin-top: 4px; margin-bottom: 8px; }
        .sub-table th, .sub-table td { font-size: 10px; }
    </style>
</head>
<body>
    <h2>Payment Collection Report</h2>

    {{-- Mother Org --}}
    <div class="org-info">
        @if($motherOrg->logo)
            <img src="{{ public_path('storage/' . $motherOrg->logo) }}" alt="Logo" class="org-logo">
        @endif
        <div class="org-header">
            <p><strong>{{ $motherOrg->name }}</strong></p>
            <p>Code: {{ $motherOrg->org_code }}</p>
        </div>
    </div>

    <p><strong>School Year:</strong> {{ \Carbon\Carbon::parse($schoolYear->sy_start)->format('Y') }} - {{ \Carbon\Carbon::parse($schoolYear->sy_end)->format('Y') }}</p>
    <p><strong>Semester:</strong> {{ ucfirst($semester->name) }}</p>

    @foreach($childOrgs as $org)
        <h3>{{ $org->name }} ({{ $org->org_code }})</h3>
        @if($org->fees->isEmpty())
            <p>No fees available.</p>
        @else
            <table class="fee-table">
                <thead>
                    <tr>
                        <th>Fee Name</th>
                        <th>Amount</th>
                        <th>Requirement Level</th>
                        <th>Status</th>
                        <th>Paid Students</th>
                        <th>Pending Students</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($org->fees as $fee)
                    <tr>
                        <td>{{ $fee->fee_name }}</td>
                        <td>PHP {{ number_format($fee->amount, 2) }}</td>
                        <td>{{ $fee->requirement_level ?? 'N/A' }}</td>
                        <td>{{ ucfirst($fee->status ?? 'pending') }}</td>
                        <td>
                            @if($fee->paid_students->isNotEmpty())
                                <table class="sub-table">
                                    @foreach($fee->paid_students as $student)
                                    <tr>
                                        <td>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</td>
                                    </tr>
                                    @endforeach
                                </table>
                            @else
                                None
                            @endif
                        </td>
                        <td>
                            @if($fee->pending_students->isNotEmpty())
                                <table class="sub-table">
                                    @foreach($fee->pending_students as $student)
                                    <tr>
                                        <td>{{ $student->last_name }}, {{ $student->first_name }} ({{ $student->student_id }})</td>
                                    </tr>
                                    @endforeach
                                </table>
                            @else
                                None
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>