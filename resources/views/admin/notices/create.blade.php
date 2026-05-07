@extends('layouts.admin')

@section('title', 'お知らせ新規作成')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'NEW NOTICE', 'title' => 'お知らせ新規作成'])
        @include('admin.notices.form', ['notice' => null])
    </div>
@endsection
