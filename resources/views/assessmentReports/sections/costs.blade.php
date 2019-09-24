@php
    $section['heading'] = null === $section['heading'] ? 'Cost Summary' : $section['heading'];
@endphp

<section class="cost-summary">

    @component('assessmentReports.sections.heading', ['section' => $section])
    @endcomponent

    <table class="cost-summary__table">
        @foreach($section['cost_items'] as $costItem)
            <tr style="box-sizing: inherit; width: 100%;">
                <td class="cost-summary__prop">{{ $costItem['cost_item']['description'] }} (ex GST):</td>
                <td class="cost-summary__value">{{ castToDollars($costItem['total_amount']) }}</td>
            </tr>
        @endforeach
        <tr class="bold">
            <td class="cost-summary__prop">Sub-Total (ex GST):</td>
            <td class="cost-summary__value">{{ castToDollars($section['cost_summary']['sub_total']) }}</td>
        </tr>
        <tr>
            <td class="cost-summary__prop">GST:</td>
            <td class="cost-summary__value">{{ castToDollars($section['cost_summary']['gst']) }}</td>
        </tr>
        <tr class="bold">
            <td class="cost-summary__prop">TOTAL:</td>
            <td class="cost-summary__value">{{ castToDollars($section['cost_summary']['total_cost']) }}</td>
        </tr>
    </table>

</section>
