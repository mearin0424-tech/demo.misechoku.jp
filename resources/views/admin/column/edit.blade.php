@extends('layouts.admin')

@section('title', 'コラム編集')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'EDIT COLUMN', 'title' => 'コラム編集'])
        @include('admin.column.form', ['categories' => $categories])
    </div>
@endsection
