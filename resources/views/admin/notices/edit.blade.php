@extends('layouts.admin')

@section('title', 'お知らせ編集')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">お知らせ編集</h1>
        @include('admin.notices.form')
    </div>
@endsection
