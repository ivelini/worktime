<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
    <thead>
    <tr>
        <th style="width: 100px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Дата</th>
        <th style="width: 120px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">День недели</th>
        <th style="width: 200px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Расписание</th>
        <th style="width: 70px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Приход</th>
        <th style="width: 70px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Уход</th>
        <th style="width: 70px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Всего</th>
    </tr>
    </thead>
    <tbody>

    @foreach($sheetTimeRows as $key => $sheetTimeRowsEmployee)
        <tr>
            <td colspan="6" style="border: 1px solid #000; background-color: #e9ecef; text-align: left; padding: 8px; font-size: 16px;">
                {{ $key }}
            </td>
        </tr>

        @foreach($sheetTimeRowsEmployee as $sheetTime)
            <tr>
                <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $sheetTime['date'] }}</td>
                <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $sheetTime['dey_of_the_week'] }}</td>
                    <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">
                        @if($sheetTime['is_night'])
                            <div>20:00-08:00 (ночное)</div>
                        @else
                            <div style="border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px;">{{ $sheetTime['schedule_name'] }}</div>
                        @endif

                    </td>
                    <td style="border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px;">{{ $sheetTime['min_time'] }}</td>
                    <td style="border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px;">{{ $sheetTime['max_time'] }}</td>

                    @if($sheetTime['date'] == 'Статистика')
                        <td style="border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">{{ $sheetTime['duration'] }}</td>
                    @else
                        <td style="border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px;">{{ $sheetTime['duration'] }}</td>
                    @endif
                </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
