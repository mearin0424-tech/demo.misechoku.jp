@extends('layouts.admin')

@section('title', 'コラム新規作成')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">コラム新規作成</h1>
        @include('admin.column.form', ['column' => null, 'categories' => $categories])
    </div>
@endsection
