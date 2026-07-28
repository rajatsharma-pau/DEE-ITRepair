<!DOCTYPE html>
<html lang="en" translate="no">
<head>
    <meta charset="utf-8">
<meta http-equiv="Content-Language" content="en">
<meta name="google" content="notranslate">
<meta name="robots" content="notranslate">  
    <title>
        Request-cum-Financial Sanction Proforma -
        {{ $request->request_no }}
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
                font-family: "DejaVu Sans", sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 0;
        }
button,
table,
th,
td,
div,
span,
strong {
    font-family: "DejaVu Sans", sans-serif;
}
        .page {
            width: 100%;
            margin: 0 auto;
            padding: 0;
            page-break-inside: avoid;
        }

        .no-print {
            text-align: center;
            margin: 15px;
        }

        .center {
            text-align: center;
        }

        .office-title {
            font-size: 17px;
            font-weight: bold;
            line-height: 1.4;
            letter-spacing: .3px;
        }

        .doc-title {
            margin-top: 14px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
        }

        .top-row {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .top-row td {
            padding: 4px 0;
        }

        .subject {
            margin-top: 18px;
            line-height: 1.55;
            font-weight: bold;
        }

        .para {
            margin-top: 14px;
            line-height: 1.65;
            text-align: justify;
        }

        .details {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            border: 1px solid #000;
            padding: 6px 7px;
            vertical-align: top;
        }

        .details th {
            width: 28%;
            text-align: left;
            background: #f5f5f5;
        }

        .encl {
            margin-top: 14px;
            line-height: 1.6;
        }

        .note {
            margin-top: 10px;
            font-size: 11px;
            line-height: 1.45;
        }

        .sign-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }

        .sign-table td {
            width: 20%;
            text-align: center;
            padding-top: 30px;
            vertical-align: bottom;
        }

        .line {
            border-top: 1px solid #000;
            display: inline-block;
            width: 105px;
            padding-top: 5px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }

            .page {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .details th {
                background: #fff;
            }
        }
    </style>
</head>

<body class="notranslate" translate="no">

@if(empty($pdfMode))
    <div class="no-print">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>
@endif
@php
    $amount = $request->proforma_amount;
    $amountText = $amount ? number_format($amount, 2) : '____________';

    $vendorName = $request->selected_vendor_name;
    $vendorText = $vendorName ? 'M/s '.$vendorName : 'the vendor concerned';

    $categoryName = optional($request->category)->name;
    $itemType = trim($request->item_type ?: '');
    $itemName = trim($request->item_name ?: '');
    $inventory = trim($request->inventory_no ?: '');
    $room = trim($request->room_no ?: '');

    $descriptionParts = [];
    if ($categoryName) { $descriptionParts[] = $categoryName; }
    if ($itemType && $itemType != $categoryName) { $descriptionParts[] = $itemType; }
    if ($itemName) { $descriptionParts[] = $itemName; }
    $itemDescription = trim(implode(' - ', $descriptionParts));
    if (!$itemDescription) { $itemDescription = 'the required item / repair work'; }

    $subjectItem = $itemDescription;
    if ($inventory) { $subjectItem .= ' (Inventory No. '.$inventory.')'; }

    $itemReference = $inventory ?: '____________';
    $employeeName = optional($request->employee)->display_name ?: '____________';
    $departmentName = optional($request->department)->name ?: optional(optional($request->employee)->department)->name;
    $schemeText = $request->scheme_name ?: '____________________________';

    $estimateDate = optional(optional($request->selectedEstimate)->estimate_date)->format('d-m-Y');
    $verifier = optional(optional($request->selectedEstimate)->programmer)->display_name ?: optional($request->programmer)->display_name;
    $verificationDate = optional(optional($request->selectedEstimate)->programmer_verified_at)->format('d-m-Y');

    $purposeLine = $itemDescription;
    if ($inventory) { $purposeLine .= ' (Inventory No. '.$inventory.')'; }
    if ($room) { $purposeLine .= ', Room No./Location: '.$room; }

    $verificationLine = 'Verified and estimate found in order';
    if ($verifier) { $verificationLine .= ' by '.$verifier; }
    if ($verificationDate) { $verificationLine .= ' on '.$verificationDate; }
    $verificationLine .= '.';
@endphp

<div class="page">
    <div class="center office-title">
        DIRECTORATE OF EXTENSION EDUCATION<br>
        PAU, LUDHIANA
    </div>

    <div class="center doc-title">REQUEST-CUM-FINANCIAL SANCTION PROFORMA</div>

    <table class="top-row">
        <tr>
            <td><strong>Request No.:</strong> {{ $request->request_no }}</td>
            <td style="text-align:right;"><strong>Date:</strong> {{ optional($request->proforma_date ?: $request->created_at)->format('d-m-Y') }}</td>
        </tr>
    </table>

    <div class="subject">
        Subject: Request for financial sanction of Rs. {{ $amountText }} for {{ strtolower($subjectItem) }}.
    </div>

    <div class="para">
        It is submitted that a request has been received from <strong>{{ $employeeName }}</strong>
        @if($departmentName)
            , <strong>{{ $departmentName }}</strong>
        @endif
        for the repair / purchase / payment of material in respect of <strong>{{ $purposeLine }}</strong>.
        The estimate / quotation has been obtained from <strong>{{ $vendorText }}</strong>
        @if($estimateDate)
            dated <strong>{{ $estimateDate }}</strong>
        @endif
        and has been physically / technically verified.
    </div>

    <table class="details">
        <tr>
            <th>Purpose / Item Details</th>
            <td>{{ $purposeLine }}</td>
        </tr>
        <tr>
            <th>Employee / Indentor</th>
            <td>
                {{ $employeeName }}
                @if($departmentName)
                    — {{ $departmentName }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Vendor / Firm</th>
            <td>{{ $vendorText }}</td>
        </tr>
        <tr>
            <th>Estimated Amount</th>
            <td>Rs. {{ $amountText }}</td>
        </tr>
        <tr>
            <th>Item Reference / Inventory No.</th>
            <td>{{ $itemReference }}</td>
        </tr>
        <tr>
            <th>Verification</th>
            <td>{{ $verificationLine }}</td>
        </tr>
    </table>

    <div class="para">
        Financial sanction amounting to <strong>Rs. {{ $amountText }}</strong> may kindly be accorded for the above-mentioned purpose as per the attached estimate / quotation of <strong>{{ $vendorText }}</strong>. The expenditure may be booked under Scheme / Budget Head: <strong>{{ $schemeText }}</strong>.
    </div>

    <div class="encl">
        <strong>Encl.:</strong> Estimate / quotation of {{ $vendorName ?: 'the vendor concerned' }}.
    </div>

    <div class="note">
        <strong>System Record:</strong> Request No. {{ $request->request_no }} | Category: {{ $categoryName ?: '-' }} | Status: {{ $request->status }} | Printed on: {{ date('d-m-Y') }}
    </div>

    <table class="sign-table">
        <tr>
            <td><span class="line">Store Keeper</span></td>
            <td><span class="line">D-4</span></td>
            <td><span class="line">Supdt.</span></td>
            <td><span class="line">AAO</span></td>
            <td><span class="line">DEE</span></td>
        </tr>
    </table>
</div>
</body>
</html>
