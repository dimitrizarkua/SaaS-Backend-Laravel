@extends('layouts.default')

@section('title','Steamatic NIS')

@section('content')
    <h2>Hurrah... You’re almost there!</h2>
    <h3>Hi {{$user->first_name}},</h3>
    @component('components.p')
        Welcome to the Steamatic NIS job management system.
    @endcomponent

    @component('components.p')
        As a security precaution, before can use the system we need to verify your identity. Please contact Steamatic IT
        support so they can set you up with the appropriate roles and permissions.
    @endcomponent

    @component('components.p')
        In the meantime feel free to check out these helpful resources and video tutorials to get you started.
    @endcomponent

    @component('components.info-bage')
        Contact IT Support on 03 9587 6333
    @endcomponent
@endsection