@extends('layouts.admin')

@section('title', 'コラム新規作成')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'NEW COLUMN', 'title' => 'コラム新規作成'])
        @include('admin.column.form', ['column' => null, 'categories' => $categories])
    </div>
@endsection
