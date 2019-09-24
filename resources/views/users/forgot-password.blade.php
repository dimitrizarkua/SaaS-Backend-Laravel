@extends('layouts.default')

@section('title','Forgot Password')

@section('content')
    <h2>Reset your password</h2>
    <h3>Hi {{$user->first_name}},</h3>
    @component('components.p')
        You’ve requested to reset your password. Click on the link below to reset your password - this link is only valid for 24-hours.
    @endcomponent

    @component('components.p')
        If you did not request to reset your password then please contact Steamatic IT support as soon as possible.
    @endcomponent

    @component('components.button', ['link'=> $resetPasswordLink])
        Reset my password
    @endcomponent
@endsection