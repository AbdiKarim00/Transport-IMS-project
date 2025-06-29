@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))

@section('link')
    <a href="{{ url('/') }}" class="text-blue-500 hover:underline">
        Go to Homepage
    </a>
@endsection
