<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Label</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            box-sizing: border-box;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .main-table td {
            vertical-align: top;
        }
        .left-column {
            width: 55%;
            padding-right: 15px;
        }
        .right-column {
            width: 45%;
            text-align: right;
            vertical-align: middle !important;
        }
        .badge {
            background-color: #1b75bc;
            color: white;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 10px;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .heading {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            letter-spacing: 1px;
        }
        .ucode {
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
            font-family: 'Courier New', Courier, monospace;
        }
        .divider {
            border: 0;
            border-bottom: 2px solid #29b4b6;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding-bottom: 5px;
        }
        .meta-value {
            font-weight: normal;
            color: #444;
        }
        .logo-text {
            color: #1b75bc;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -2px;
            line-height: 1;
        }
        .logo-subtext {
            color: #29b4b6;
            font-size: 11px;
            display: block;
            letter-spacing: 0;
            font-weight: normal;
        }
        .code-image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<table class="main-table">
    <tr>
        <td class="left-column">
            <div class="ucode">{{ $model->name ?? '' }}</div>
            
            <hr class="divider">
            
            <table class="meta-table">
                <tr>
                    <td>INTERNAL CODE.</td>
                    <td class="meta-value">{{ $model->ucode ?? 'N/A' }}</td> </tr>
                <tr>
                    <td>UNIQUE CODE.</td>
                    <td class="meta-value">{{ $model->code ?? 'N/A' }}</td> </tr>
            </table>
        </td>

        <td class="right-column">
            @if($type === 'qr')
                <img src="{{ $qrPath }}" class="code-image" style="width: 170px; height: 170px;">
            @else
                <img src="{{ $barcodePath }}" class="code-image" style="width: 190px; height: 60px; margin-top: 50px;">
            @endif
        </td>
    </tr>
</table>

</body>
</html>