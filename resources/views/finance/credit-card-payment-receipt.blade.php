@extends('layouts.default')

@section('title','Credit Card payment has been processed')

@section('content')
    <p>Thank you for your payment. Your receipt details are below:</p>
    <p><b>Date:</b> {{date_format($receipt['paidAt'], 'd-m-Y')}}</p>
    <p><b>Job:</b> {{$receipt['jobId'] === null ? '-' : $receipt['jobId']}}</p>
    <p><b>Receipt No.</b> {{$receipt['externalTransactionId']}}</p>
    <p><b>Amount:</b> ${{number_format($receipt['amount'], 2, '.', ',')}}</p>
@endsection
