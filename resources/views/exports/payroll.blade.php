<table>
    <thead>
    <tr>
        <th style="width: 50px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">#</th>
        <th style="width: 50px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">ID</th>
        <th style="width: 200px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">ФИО</th>
        <th style="width: 200px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Должность</th>
        <th style="width: 100px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Оклад</th>
        <th style="width: 100px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Аванс</th>
        <th style="width: 100px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Отработано</th>
        <th style="width: 150px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Ставка за час</th>
        <th style="width: 100px; border: 1px solid #000; text-align: center; padding: 8px; font-size: 12px; font-weight: bold;">Оплата</th>
    </tr>
    </thead>
    <tbody>
    @foreach($salaryPayEmployees as $index => $payEmployee)
        <tr>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->emp_code }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->fio }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->position }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->salary_amount }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->advance }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->month_duration }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->per_pay_hour_display }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">{{ $payEmployee->salary_pay }}</td>
        </tr>
    @endforeach

    <tr>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;">Итого</td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px; font-weight: bold;">{{ $fullAdvance }}</td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px;"></td>
        <td style="border: 1px solid #000; text-align: left; padding: 8px; font-size: 12px; font-weight: bold;">{{ $fullSalaryPay }}</td>
    </tr>
    </tbody>
</table>
