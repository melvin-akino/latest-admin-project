@extends('layouts.mail')

@section('content')
    <div class="flex w-full py-6 px-10 mt-6 items-center justify-center bg-orange">
        &nbsp;

        <div class="absolute shadow-md rounded-lg py-2 px-4 bg-white inset-auto mt-8 items-center justify-center">
            <img class="object-center" width="75" src="{{ asset('images/logo.png') }}">
        </div>
    </div>

    <div class="w-full pt-8 pb-2 px-10">
        <p>Greetings!,</p><br>

        <p>Something went wrong with the <strong>Currency Conversion API</strong>. Please check the logs for more information.</p>

        <p>Thank you for using Multiline.</p>
    </div>

    <div class="w-full py-8 px-10">
        <span>Regards,<br />Multiline Admin</span>
    </div>
@endsection
