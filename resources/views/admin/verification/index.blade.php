@extends('layouts.app')

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
                                <form method="POST" action="{{ route('bk.verification.cast.approve') }}">
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

        <section class="admin-panel" style="margin-top: 16px;">
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
                                    <form method="POST" action="{{ route('bk.verification.shopdoc.approve') }}">
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

    <style>
        .admin-page { padding: 24px 0; }
        .admin-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #e5e7eb;
        }
        .admin-alert {
            background: rgba(55, 65, 81, 0.6);
            border: 1px solid rgba(156, 163, 175, 0.9);
            color: #e5e7eb;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            font-size: 0.85rem;
        }
        .admin-panel {
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
            margin-bottom: 12px;
        }
        .admin-panel-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 8px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            color: #e5e7eb;
        }
        .admin-table th,
        .admin-table td {
            border-bottom: 1px solid rgba(55, 65, 81, 0.9);
            padding: 6px 8px;
            text-align: left;
        }
        .admin-table th {
            background: rgba(31, 41, 55, 0.7);
            font-weight: 600;
        }
        .btn-action.manage {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        .text-xs { font-size: 0.75rem; }
        .text-gray-400 { color: #9ca3af; }
    </style>
@endsection

