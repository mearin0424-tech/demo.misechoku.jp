@extends('layouts.admin')

@section('title', 'お知らせ編集')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'EDIT NOTICE', 'title' => 'お知らせ編集'])
        @include('admin.notices.form')
    </div>
@endsection
