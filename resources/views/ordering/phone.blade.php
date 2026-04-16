<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body style="background:#f6f7fb;">
<div class="container py-4" style="max-width:520px;">
    <div class="card shadow-sm border-0"><div class="card-body">
        <h4 class="mb-3">Verify Contact</h4>
        <div class="mb-2"><label>Email Address</label><input id="email" type="email" class="form-control" placeholder="you@example.com" required></div>
        <div class="mb-2"><label>Phone Number</label><input id="phone" class="form-control" placeholder="e.g. 9876543210"></div>
        <button id="sendOtp" class="btn btn-success w-100 mb-3">Send OTP</button>
        <div id="otpBlock" class="d-none">
            <div class="mb-2"><label>OTP</label><input id="otp" class="form-control" maxlength="6"></div>
            <button id="verifyOtp" class="btn btn-success w-100">Verify & Continue</button>
        </div>
    </div></div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#sendOtp').on('click', function(){
    $.post("{{ route('ordering.otp.send') }}",{_token:'{{ csrf_token() }}', email:$('#email').val(), phone:$('#phone').val()})
        .done(function(){ $('#otpBlock').removeClass('d-none'); Swal.fire('OTP sent','Check your email inbox for OTP.','success'); })
        .fail(function(x){ Swal.fire('Error', x.responseJSON?.message || 'Unable to send OTP','error'); });
});
$('#verifyOtp').on('click', function(){
    $.post("{{ route('ordering.otp.verify') }}",{_token:'{{ csrf_token() }}', otp:$('#otp').val()})
        .done(function(res){ window.location.href = res.redirect; })
        .fail(function(x){ Swal.fire('Error', x.responseJSON?.message || 'Invalid OTP','error'); });
});
</script>
</body>
</html>
