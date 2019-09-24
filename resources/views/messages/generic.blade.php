@extends('layouts.default')

@section('title','Message from Steamatic')

@section('content')
    <h2>Hello, you have a new message from Steamatic:</h2>

    @component('components.p')
        {!! $message_content !!}
    @endcomponent
@endsection
