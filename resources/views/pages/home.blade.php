@extends('layouts.main')
@section('title','Trang chủ')
@section('main_class','no-offset')
@section('content')

<video style="height: 100vh; width: 100%; object-fit: cover;" autoplay loop muted playsinline>
    <source src="{{ asset('assets/video/background.mp4') }}" type="video/mp4">
    Trình duyệt không hỗ trợ Video.
</video>

@endsection
