@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Server Error'))

@section('link')
    <a href="{{ url('/') }}" class="text-blue-500 hover:underline">
        Go to Homepage
    </a>
@endsection
