@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))

@section('link')
    <a href="{{ url('/') }}" class="text-blue-500 hover:underline">
        Go to Homepage
    </a>
@endsection
