@extends('layouts.admin')

@section('title', 'お知らせ新規作成')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">お知らせ新規作成</h1>
        @include('admin.notices.form', ['notice' => null])
    </div>
@endsection
