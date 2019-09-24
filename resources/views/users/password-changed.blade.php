@extends('layouts.default')

@section('title','Password has been reset')

@section('content')
    <h2>Password has been reset</h2>
    <h3>Hi {{$user->first_name}},</h3>
    @component('components.p')
        This is a security alert to let you know that your password has been reset.
    @endcomponent

    @component('components.p')
        If you did not reset your password then please immediately contact Steamatic IT support on 03 9587 6333.
    @endcomponent

    @component('components.button', ['link'=> $url])
        Log into Steamatic NIS
    @endcomponent
@endsection