@extends('layouts.default')

@section('title','Welcome')

@section('content')
    <h3>Hello {{$user->first_name}},</h3>
    @component('components.p')
        And welcome to Steamatic. Your account has been created.
    @endcomponent

    @component('components.button', ['link'=> $url])
        Log into Steamatic NIS
    @endcomponent
@endsection