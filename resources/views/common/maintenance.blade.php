@extends('layouts.app-v2')

@section('title', '準備中')

@section('content')
<div class="maintenance-screen-wrap">
    <img src="{{ asset('assets/images/guide/maintenance-screen.png') }}" alt="準備中です" class="maintenance-screen-img">
</div>
@endsection

@push('styles')
<style>
.maintenance-screen-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 50vh;
    padding: 20px;
    background: #0a0a0a;
}
.maintenance-screen-img {
    width: 100%;
    max-width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}
</style>
@endpush
