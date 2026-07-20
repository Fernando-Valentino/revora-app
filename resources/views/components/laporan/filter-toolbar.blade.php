@props([
    'summary',
    'metrics',
    'rayons',
    'rayonId',
    'startDate',
    'endDate',
    'type' => 'harian',
    'readonly' => false,
    'pdfRoute' => null,
    'excelRoute' => null,
])

@php
    use Carbon\Carbon;
    $parsedStart = Carbon::parse($startDate);
    $curYear    = $parsedStart->year;
    $curMonth   = $parsedStart->month;
    $curWeek    = max(1, min(5, (int) ceil($parsedStart->day / 7)));

    $monthNames = [
        1=>'Januari',  2=>'Februari', 3=>'Maret',    4=>'April',
        5=>'Mei',      6=>'Juni',     7=>'Juli',      8=>'Agustus',
        9=>'September',10=>'Oktober', 11=>'November', 12=>'Desember'
    ];
@endphp

{{-- Filter & Export Toolbar --}}
<form method="GET" action="" class="toolbar" id="laporan-filter-form"
      onsubmit="event.preventDefault(); submitLaporanFilterAjax();">

    {{-- Hidden date fields that backend reads via request()->input() --}}
    <input type="hidden" name="start_date" id="hidden-start-date" value="{{ $startDate }}" />
    <input type="hidden" name="end_date"   id="hidden-end-date"   value="{{ $endDate }}" />

    {{-- ===== HARIAN: date range ===== --}}
    <div id="period-harian" class="period-inputs"
         style="{{ $type !== 'harian' ? 'display:none;' : '' }}">
        <span class="toolbar-label">Periode</span>
        <input type="date" id="input-start-date" value="{{ $startDate }}" title="Tanggal Mulai" />
        <span class="toolbar-sep">&ndash;</span>
        <input type="date" id="input-end-date"   value="{{ $endDate }}"   title="Tanggal Akhir" />
    </div>

    {{-- ===== MINGGUAN: tahun · bulan · minggu ke- ===== --}}
    <div id="period-mingguan" class="period-inputs"
         style="{{ $type !== 'mingguan' ? 'display:none;' : '' }}">
        <span class="toolbar-label">Tahun</span>
        <input type="number" id="input-year-mingguan" value="{{ $curYear }}" min="2020" max="2035" />
        <span class="toolbar-sep">·</span>
        <span class="toolbar-label">Bulan</span>
        <select id="input-month-mingguan">
            @foreach($monthNames as $num => $name)
                <option value="{{ $num }}" {{ $curMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        <span class="toolbar-sep">·</span>
        <span class="toolbar-label">Minggu ke</span>
        <select id="input-week-mingguan">
            @for($w = 1; $w <= 5; $w++)
                <option value="{{ $w }}" {{ $curWeek == $w ? 'selected' : '' }}>{{ $w }}</option>
            @endfor
        </select>
    </div>

    {{-- ===== BULANAN: tahun · bulan ===== --}}
    <div id="period-bulanan" class="period-inputs"
         style="{{ $type !== 'bulanan' ? 'display:none;' : '' }}">
        <span class="toolbar-label">Tahun</span>
        <input type="number" id="input-year-bulanan" value="{{ $curYear }}" min="2020" max="2035" />
        <span class="toolbar-sep">·</span>
        <span class="toolbar-label">Bulan</span>
        <select id="input-month-bulanan">
            @foreach($monthNames as $num => $name)
                <option value="{{ $num }}" {{ $curMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ===== TAHUNAN: tahun saja ===== --}}
    <div id="period-tahunan" class="period-inputs"
         style="{{ $type !== 'tahunan' ? 'display:none;' : '' }}">
        <span class="toolbar-label">Tahun</span>
        <input type="number" id="input-year-tahunan" value="{{ $curYear }}" min="2020" max="2035" />
    </div>

    {{-- Wilayah --}}
    <div class="field">
        <span>Wilayah</span>
        <select name="rayon_id">
            <option value="0" {{ $rayonId == 0 ? 'selected' : '' }}>Semua Rayon</option>
            @foreach($rayons as $r)
                <option value="{{ $r->id }}" {{ $rayonId == $r->id ? 'selected' : '' }}>{{ $r->nama_rayon }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tipe --}}
    <div class="field">
        <span>Tipe</span>
        <select name="type" id="filter-type-select">
            <option value="harian"   {{ $type == 'harian'   ? 'selected' : '' }}>Harian</option>
            <option value="mingguan" {{ $type == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
            <option value="bulanan"  {{ $type == 'bulanan'  ? 'selected' : '' }}>Bulanan</option>
            <option value="tahunan"  {{ $type == 'tahunan'  ? 'selected' : '' }}>Tahunan</option>
        </select>
    </div>

    <div class="spacer"></div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ request()->url() }}" class="btn-reset" title="Reset Filter">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </a>
        @if($pdfRoute)
            <a href="{{ $pdfRoute }}" class="btn-export-pdf" id="btn-export-pdf">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        @endif
        @if(!$readonly && $excelRoute)
            <a href="{{ $excelRoute }}" class="btn-export-excel" id="btn-export-excel">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
        @endif
    </div>
</form>

{{-- Persistent meta area for AJAX grabs --}}
<div id="report-meta-area" style="display: none;">
    <div id="meta-periode">{{ $summary['periode'] }}</div>
</div>

<script>
/**
 * Compute and write start_date / end_date hidden inputs
 * from the currently visible dynamic period selectors.
 * Must be called before reading form values for AJAX submit.
 */
function computePeriodDates() {
    var typeEl = document.getElementById('filter-type-select');
    if (!typeEl) return;
    var type = typeEl.value;

    function _fmt(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    }

    var startDate = '', endDate = '';

    if (type === 'harian') {
        startDate = (document.getElementById('input-start-date') || {}).value || '';
        endDate   = (document.getElementById('input-end-date')   || {}).value || '';

    } else if (type === 'mingguan') {
        var year  = parseInt((document.getElementById('input-year-mingguan')  || {}).value) || new Date().getFullYear();
        var month = parseInt((document.getElementById('input-month-mingguan') || {}).value) || 1;
        var week  = parseInt((document.getElementById('input-week-mingguan')  || {}).value) || 1;
        var dayS  = (week - 1) * 7 + 1;
        var dayE  = Math.min(week * 7, new Date(year, month, 0).getDate());
        startDate = _fmt(new Date(year, month - 1, dayS));
        endDate   = _fmt(new Date(year, month - 1, dayE));

    } else if (type === 'bulanan') {
        var year  = parseInt((document.getElementById('input-year-bulanan')  || {}).value) || new Date().getFullYear();
        var month = parseInt((document.getElementById('input-month-bulanan') || {}).value) || 1;
        var lastD = new Date(year, month, 0).getDate();
        startDate = year + '-' + String(month).padStart(2, '0') + '-01';
        endDate   = year + '-' + String(month).padStart(2, '0') + '-' + String(lastD).padStart(2, '0');

    } else if (type === 'tahunan') {
        var year = parseInt((document.getElementById('input-year-tahunan') || {}).value) || new Date().getFullYear();
        startDate = year + '-01-01';
        endDate   = year + '-12-31';
    }

    if (startDate) document.getElementById('hidden-start-date').value = startDate;
    if (endDate)   document.getElementById('hidden-end-date').value   = endDate;
}

/**
 * Show the correct period input panel, hide the others.
 */
function switchPeriodInputs(type) {
    ['harian', 'mingguan', 'bulanan', 'tahunan'].forEach(function(t) {
        var el = document.getElementById('period-' + t);
        if (el) el.style.display = (t === type) ? 'flex' : 'none';
    });
}

// Wire type-select to switch panels (submit handled via event delegation in parent view)
(function() {
    var typeSelect = document.getElementById('filter-type-select');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            switchPeriodInputs(this.value);
        });
    }
})();
</script>
