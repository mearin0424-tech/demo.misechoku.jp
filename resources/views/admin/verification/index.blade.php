@extends('layouts.admin')

@section('title', '本人確認・書類審査')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">本人確認・書類審査</h1>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel">
            <h2 class="admin-panel-title">キャスト本人確認ステータス</h2>
            @php
                $castStatus = $castIdentityStatus;
                $castLabelForCast = [
                    'not_submitted' => '未提出',
                    'pending'       => '提出済み（未承認）',
                    'approved'      => '提出済み',
                ][$castStatus] ?? '未提出';
                $castLabelForAdmin = [
                    'not_submitted' => '提出待ち',
                    'pending'       => '未承認',
                    'approved'      => '承認済み',
                ][$castStatus] ?? '提出待ち';
            @endphp
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>アクター</th>
                        <th>ステータス（キャスト側表示）</th>
                        <th>ステータス（運営側表示）</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>キャスト</td>
                        <td>{{ $castLabelForCast }}</td>
                        <td>{{ $castLabelForAdmin }}</td>
                        <td>
                            @if($castStatus !== 'approved')
                                <form method="POST" action="{{ route('admin.verification.cast.approve') }}">
                                    @csrf
                                    <button type="submit" class="btn-action manage">
                                        承認する
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">承認済み</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">店舗の必要書類提出ステータス</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>書類</th>
                        <th>店舗側ステータス</th>
                        <th>運営側ステータス</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $docNames = [
                            'business_license' => '営業許可証',
                            'adult_entertainment_license' => '風営許可証',
                        ];
                    @endphp
                    @foreach($shopDocStatus as $key => $status)
                        @php
                            $shopLabel = [
                                'not_submitted' => '未提出',
                                'pending'       => '提出済み（未承認）',
                                'approved'      => '提出済み',
                            ][$status] ?? '未提出';
                            $adminLabel = [
                                'not_submitted' => '提出待ち',
                                'pending'       => '未承認',
                                'approved'      => '承認済み',
                            ][$status] ?? '提出待ち';
                        @endphp
                        <tr>
                            <td>{{ $docNames[$key] ?? $key }}</td>
                            <td>{{ $shopLabel }}</td>
                            <td>{{ $adminLabel }}</td>
                            <td>
                                @if($status === 'pending')
                                    <form method="POST" action="{{ route('admin.verification.shopdoc.approve') }}">
                                        @csrf
                                        <input type="hidden" name="type" value="{{ $key }}">
                                        <button type="submit" class="btn-action manage">
                                            承認する
                                        </button>
                                    </form>
                                @elseif($status === 'approved')
                                    <span class="text-xs text-gray-400">承認済み</span>
                                @else
                                    <span class="text-xs text-gray-400">店舗からの提出待ち</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
@endsection

