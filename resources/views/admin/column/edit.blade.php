@extends('layouts.admin')

@section('title', 'コラム編集')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">コラム編集</h1>
        @include('admin.column.form')
    </div>
@endsection
