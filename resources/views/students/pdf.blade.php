<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Records</title>
    <style>
        @page {
            margin: 30px 32px 42px;
        }

        body {
            color: #243247;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .report-header {
            border-bottom: 2px solid #4054d6;
            margin-bottom: 20px;
            padding-bottom: 12px;
            text-align: center;
        }

        h1 {
            color: #17243a;
            font-size: 22px;
            margin: 0 0 5px;
        }

        .subtitle {
            color: #5b6cf9;
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        .summary {
            color: #64748b;
            font-size: 9px;
            margin: 0 0 10px;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #d9e0ea;
            overflow-wrap: break-word;
            padding: 8px 7px;
            text-align: left;
        }

        th {
            background: #17243a;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #f3f6fb;
        }

        tr {
            page-break-inside: avoid;
        }

        .empty-state {
            border: 1px dashed #cbd5e1;
            color: #64748b;
            padding: 24px;
            text-align: center;
        }

        .id-column { width: 8%; }
        .name-column { width: 18%; }
        .email-column { width: 27%; }
        .phone-column { width: 14%; }
        .course-column { width: 23%; }
        .age-column { width: 10%; }
    </style>
</head>
<body>
    <header class="report-header">
        <h1>Student Management System</h1>
        <p class="subtitle">Student Records</p>
    </header>

    <p class="summary">{{ $students->count() }} {{ \Illuminate\Support\Str::plural('student record', $students->count()) }} included in this report.</p>

    @if ($students->isEmpty())
        <div class="empty-state">
            @if ($filtersApplied)
                No student records match the selected filters. Try adjusting or clearing your filters.
            @else
                No student records are available yet.
            @endif
        </div>
    @else
        <table>
            <colgroup>
                <col class="id-column">
                <col class="name-column">
                <col class="email-column">
                <col class="phone-column">
                <col class="course-column">
                <col class="age-column">
            </colgroup>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Age</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone ?: 'Not provided' }}</td>
                        <td>{{ $student->course }}</td>
                        <td>{{ $student->age }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
