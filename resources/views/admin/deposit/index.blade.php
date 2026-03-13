@extends('layouts.admin')

@section('title', '入金・振込管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">入金・振込管理</h1>
        <p class="admin-description">
            店舗からの入金状況と、キャストへの振込ステータスを管理する画面です。<br>
            現在はデモデータとステータスフローを表示しており、後続で実データ連携（`Deposit` 周り）を差し込みます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel">
            <h2 class="admin-panel-title">現在の入金ステータス</h2>
            @php $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働']; @endphp
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>アクター</th>
                        <th>ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>キャスト</td><td>{{ $flow['cast'] }}</td></tr>
                    <tr><td>店舗</td><td>{{ $flow['shop'] }}</td></tr>
                    <tr><td>運営</td><td>{{ $flow['admin'] }}</td></tr>
                </tbody>
            </table>
            @php $step = session('deposit_flow_step', 0); @endphp
            <div class="management-actions">
                @if($step == 2)
                    <form method="POST" action="{{ route('admin.deposits.approve') }}">
                        @csrf
                        <button type="submit" class="btn-action manage">
                            入金額を承認する
                        </button>
                    </form>
                @elseif($step == 4)
                    <form method="POST" action="{{ route('admin.deposits.paycast') }}">
                        @csrf
                        <button type="submit" class="btn-action manage">
                            キャストへの振込を実行する
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店舗</th>
                        <th>キャスト</th>
                        <th>ステータス</th>
                        <th>金額</th>
                        <th>依頼日</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit['id'] }}</td>
                            <td>{{ $deposit['shop_name'] }}</td>
                            <td>{{ $deposit['cast_name'] }}</td>
                            <td>{{ $deposit['status'] }}</td>
                            <td>{{ number_format($deposit['amount']) }} 円</td>
                            <td>{{ optional($deposit['requested_at'])->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">入金データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

