@php
    $shiftStart = old('shift_time_start', $recruit['shift_time_start'] ?? '');
    $shiftEnd = old('shift_time_end', $recruit['shift_time_end'] ?? '');
    $endLastRaw = old('shift_end_is_last', !empty($recruit['shift_end_is_last']) ? '1' : '0');
    $endLast = $endLastRaw === '1' || $endLastRaw === 1 || $endLastRaw === true;
@endphp
<div class="job-edit-v2__field">
    <span class="job-edit-v2__label">勤務時間（開始〜終了） <span class="job-edit-v2__req">必須</span></span>
    <div class="job-edit-v2__shift-grid">
        <div class="job-edit-v2__shift-cell">
            <label class="job-edit-v2__hint" for="shift_time_start" style="display:block;margin-bottom:4px;">開始</label>
            <input type="time" id="shift_time_start" name="shift_time_start" step="60" class="job-edit-v2__input"
                   value="{{ $shiftStart }}" required>
        </div>
        <div class="job-edit-v2__shift-cell job-edit-v2__shift-cell--end">
            <label class="job-edit-v2__hint" for="shift_time_end" style="display:block;margin-bottom:4px;">終了</label>
            <input type="time" id="shift_time_end" name="shift_time_end" step="60" class="job-edit-v2__input js-shift-end-time"
                   value="{{ $shiftEnd }}" @if($endLast) disabled @endif>
            <label class="job-edit-v2__shift-last">
                <input type="checkbox" name="shift_end_is_last" value="1" class="js-shift-end-last" id="shift_end_is_last" {{ $endLast ? 'checked' : '' }}>
                <span>LAST（ラストまで）</span>
            </label>
        </div>
    </div>
    <p class="job-edit-v2__hint">終了が「ラスト」のときは LAST を選び、終了時刻は空にできます。</p>
    @error('shift_time_start')
        <p class="job-edit-v2__hint" style="color:#f87171;">{{ $message }}</p>
    @enderror
    @error('shift_time_end')
        <p class="job-edit-v2__hint" style="color:#f87171;">{{ $message }}</p>
    @enderror
</div>
