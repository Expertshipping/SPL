<table>
    {{--    <tr>--}}
    {{--        <td>!TIMERHDR</td>--}}
    {{--        <td>VER</td>--}}
    {{--        <td>REL</td>--}}
    {{--        <td>COMPANYNAME</td>--}}
    {{--        <td>IMPORTEDBEFORE</td>--}}
    {{--        <td>FROMTIMER</td>--}}
    {{--        <td>COMPANYCREATETIME</td>--}}
    {{--        <td></td>--}}
    {{--        <td></td>--}}
    {{--        <td></td>--}}
    {{--    </tr>--}}
    {{--    <tr>--}}
    {{--        <td>TIMERHDR</td>--}}
    {{--        <td>8</td>--}}
    {{--        <td>0</td>--}}
    {{--        <td>Import Test Company</td>--}}
    {{--        <td>N</td>--}}
    {{--        <td>Y</td>--}}
    {{--        <td>1208544781</td>--}}
    {{--        <td></td>--}}
    {{--        <td></td>--}}
    {{--        <td></td>--}}
    {{--    </tr>--}}
    {{--    <tr>--}}
    {{--        <td>!HDR</td>--}}
    {{--        <td>PROD</td>--}}
    {{--        <td>VER</td>--}}
    {{--        <td>REL</td>--}}
    {{--        <td>IIFVER</td>--}}
    {{--        <td>DATE</td>--}}
    {{--        <td>TIME</td>--}}
    {{--        <td>ACCNTNT</td>--}}
    {{--        <td>ACCNTNTSPLITTIME</td>--}}
    {{--        <td></td>--}}
    {{--    </tr>--}}
    {{--    <tr>--}}
    {{--        <td>HDR</td>--}}
    {{--        <td>QuickBooks Pro for Windows</td>--}}
    {{--        <td>Version 6.0D</td>--}}
    {{--        <td>Release R4P</td>--}}
    {{--        <td>1</td>--}}
    {{--        <td>4/18/08</td>--}}
    {{--        <td>1208545205</td>--}}
    {{--        <td>N</td>--}}
    {{--        <td>0</td>--}}
    {{--        <td></td>--}}
    {{--    </tr>--}}
    <tr>
        <td>!TIMEACT</td>
        <td>DATE</td>
        <td>JOB</td>
        <td>EMP</td>
        <td>ITEM</td>
        <td>PITEM</td>
        <td>DURATION</td>
        <td>PROJ</td>
        <td>NOTE</td>
        <td>BILLINGSTATUS</td>
    </tr>
    @foreach ($agentCommissions as $agentCommission)
        <tr>
            <td>TIMEACT</td>
            <td>{{ $agentCommission['created_at'] }}</td>
            <td>Customer</td>
            <td>{{ $agentCommission['name'] }}</td>
            <td>Labor</td>
            <td>{{ $agentCommission['commission'] }}</td>
            <td>{{ $agentCommission['amount'] }}</td>
            <td></td>
            <td>Notes about the activity</td>
            <td>0</td>
        </tr>
    @endforeach
</table>
