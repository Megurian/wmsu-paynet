<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ddd; padding:6px; }
        th { background:#f3f4f6; }
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

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            @if($tab === 'payments')
                <th>Fee</th>
                <th>Amount</th>
                <th>Status</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
        <tr>
            <td>{{ $index+1 }}</td>
            <td>
                {{ $tab === 'payments'
                    ? $row->student->last_name.', '.$row->student->first_name
                    : $row->student->last_name.', '.$row->student->first_name }}
            </td>

            @if($tab === 'payments')
                <td>{{ $row->fees[0]->fee_name }}</td>
                <td>{{ number_format($row->fees[0]->pivot->amount_paid,2) }}</td>
                <td>
                    {{ $row->fees[0]->pivot->amount_paid > 0 ? 'Paid' : 'Unpaid' }}
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>