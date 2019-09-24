<div style="box-sizing: inherit; margin: 30px 0; page-break-inside: avoid">

    <table class="semi-table">
        <tr>
            <td class="bold semi-table__prop">Prepared For:</td>
        </tr>
        <tr>
            <td>{{ $customer['name'] }}</td>
        </tr>
        <tr>
            <td>{{ $customer['address'] }}</td>
        </tr>
    </table>

    @component('assessmentReports.sections.spacer')
    @endcomponent

    <table class="semi-table">
        <tr>
            <td class="bold semi-table__prop">Reference No.</td>
            <td>{{ $job['reference'] }}</td>
        </tr>
        <tr>
            <td class="bold semi-table__prop">Steamatic Ref.</td>
            <td>{{ $job['claim'] }}</td>
        </tr>
    </table>

    @component('assessmentReports.sections.spacer')
    @endcomponent

    <table class="semi-table">
        <tr>
            <td class="bold semi-table__prop">Customer:</td>
            <td>{{ $customer['name'] }}</td>
        </tr>
        <tr>
            <td class="bold semi-table__prop">Site Address:</td>
            <td>{{ $job['site_address'] }}</td>
        </tr>
    </table>

    @component('assessmentReports.sections.spacer')
    @endcomponent

</div>
