<form id="reset-grid-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="target" value="grid_search">
</form>
<form id="reset-gwo-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="target" value="gwo">
</form>
<form id="delete-optimasi-run-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="delete-run-id" value="">
</form>
