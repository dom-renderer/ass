<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body style="background:#f6f7fb;">
<div class="container py-4" style="max-width:780px;">
    <div class="card shadow-sm border-0 mb-3"><div class="card-body text-center">
        <h3 class="mb-1">Start Ordering</h3><p class="text-muted mb-3">Choose how you want to start your order</p>
        <div class="row g-3">
            <div class="col-md-6"><button class="btn btn-success w-100 py-3" id="openScanner">1. Scan</button></div>
            <div class="col-md-6"><button class="btn btn-outline-success w-100 py-3" id="openManual">2. Manually Select Store/Table</button></div>
        </div>
    </div></div>

    <div class="card shadow-sm border-0 mb-3 d-none" id="scannerCard"><div class="card-body">
        <h5>Scan QR</h5><div id="qr-reader" style="width:100%"></div>
        <div class="text-muted small mt-2">Allow camera permission to scan table QR.</div>
    </div></div>

    <div class="card shadow-sm border-0 d-none" id="manualCard"><div class="card-body">
        <h5>Manual Selection</h5>
        <form method="POST" action="{{ route('ordering.manual') }}" id="manualForm">@csrf
            <div class="mb-2"><label>Store</label><select name="store_id" id="store_id" class="form-control" required><option value="">Select store</option>@foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-2"><label>Table Number</label><select name="table_number" id="table_no" class="form-control" required><option value="">Select table</option></select></div>
            <button class="btn btn-success w-100">Continue</button>
        </form>
    </div></div>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#openScanner').on('click', function(){ $('#scannerCard').removeClass('d-none'); $('#manualCard').addClass('d-none'); startScanner(); });
$('#openManual').on('click', function(){ $('#manualCard').removeClass('d-none'); $('#scannerCard').addClass('d-none'); });

function startScanner(){
    const html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: 230 });
    html5QrcodeScanner.render(function(decodedText){
        $.post("{{ route('ordering.qr.resolve') }}", {_token:'{{ csrf_token() }}', value:decodedText})
            .done(function(res){ window.location.href = res.redirect; })
            .fail(function(){ Swal.fire('Not found','QR does not match any table. Use manual option.','warning'); });
    }, function(){});
}

$('#store_id').on('change', function(){
    const id=$(this).val(); $('#table_no').html('<option>Loading...</option>');
    if(!id){ $('#table_no').html('<option value="">Select table</option>'); return; }
    $.get("{{ route('ordering.store-tables') }}", {store_id:id}, function(res){
        let html='<option value="">Select table</option>';
        (res.tables||[]).forEach(t=> html += '<option value="'+t.table_number+'">Table '+t.table_number+' ('+t.qr_label+')</option>');
        $('#table_no').html(html);
    });
});
</script>
</body>
</html>
