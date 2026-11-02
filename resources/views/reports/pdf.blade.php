<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }}</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 19px; color: #4338ca; }
        .header p { margin: 4px 0 0; color: #64748b; font-size: 10px; }
        .summary { display: inline-block; margin-bottom: 12px; }
        .summary span { display: inline-block; background: #eef2ff; color: #3730a3; border-radius: 12px; padding: 4px 12px; margin: 0 6px 6px 0; font-size: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background: #4f46e5; color: #fff; padding: 7px 9px; text-align: left; font-size: 10px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px 9px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ strtoupper($report['title']) }}</h1>
        <p>{{ $report['period_label'] }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        @foreach($report['summary'] as $label => $value)
            <span>{{ $label }}: {{ $value }}</span>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>@foreach($report['headers'] as $h)<th>{{ $h }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @foreach($report['rows'] as $row)
            <tr>
                @foreach($row as $cell)
                    @if(\Illuminate\Support\Str::startsWith($cell, 'BADGE:'))
                        @php $parts = explode(':', $cell, 3); @endphp
                        <td>{{ $parts[2] ?? '' }}</td>
                    @else
                        <td>{{ $cell }}</td>
                    @endif
                @endforeach
            </tr>
            @endforeach
            @if(count($report['rows']) === 0)
            <tr><td colspan="{{ count($report['headers']) }}" style="text-align:center; color:#94a3b8;">— Tidak ada data —</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Laporan dihasilkan otomatis oleh aplikasi {{ config('app.name') }}
    </div>
</body>
</html>
