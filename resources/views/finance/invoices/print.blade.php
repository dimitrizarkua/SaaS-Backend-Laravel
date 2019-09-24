@extends('finance.layout')

@section('title')
    @if($isDraft)
        <strong>Draft</strong>
    @endif
    Tax invoice #{{$invoiceId}}
@endsection

@section('entityName', 'Invoice')

@section('items')
    @foreach($items as $item)
        @component('finance.invoices.item', ['item'=> $item])
        @endcomponent
    @endforeach
@endsection

@section('total-section')
    <div style="margin-bottom: 60px; height: 160px;position: relative;">
        <table style="text-transform: uppercase;
            margin: 0 0 0 auto;
            border: 1px solid transparent;
            border-collapse: collapse;">
            <tr>
                <td style="text-align: right;
                font-size: 12px;
                padding-bottom: 15px;
                padding-left: 40px;"><strong>SUB-TOTAL EX.</strong></td>
                <td style="padding: 0 15px 15px 70px;
                text-align: right;">$ {{ number_format($subTotal, 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <td style="text-align: right;
                font-size: 12px;
                padding-bottom: 15px;
                padding-left: 40px;"><strong>GST</strong></td>
                <td style="padding: 0 15px 15px 70px;
                text-align: right;">$ {{ number_format($taxes, 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="height: 1px;
                   width: 80%;
                   float: right;
                   background: #D3DBDE;"></div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;
                font-size: 12px;
                padding-left: 40px;"><strong>TOTAL NET PAYMENTS</strong></td>
                <td style="padding: 15px 15px 15px 70px;
                text-align: right;">$ {{number_format($totalNet, 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <td style="text-align: right;border-width: 1px 0px 1px 1px;border-color: black;border-style: solid;
                padding-left: 40px;"><strong>AMOUNT DUE</strong></td>
                <td style="padding: 15px 15px 15px 70px;border-width: 1px 1px 1px 0px;border-color: black;border-style: solid;
                font-size: 16px;
                text-align: right;"><strong>$ {{ number_format($amountDue, 2, '.', ',') }}</strong></td>
            </tr>
        </table>
    </div>
@endsection

@section('additional-data')
    {{-- Due Date --}}
    <div style="margin-bottom: 30px;font-size: 18px;">
        <strong>Due Date: {{$dueDate}}</strong>
    </div>
@endsection
