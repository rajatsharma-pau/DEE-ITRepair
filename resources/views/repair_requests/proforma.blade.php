<!DOCTYPE html>
<html lang="pa">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Language" content="pa">

    <title>ਵਿੱਤੀ ਮਨਜ਼ੂਰੀ ਹੁਕਮ - {{ $request->request_no }}</title>

    <style>
        @font-face {
            font-family: "NotoGurmukhi";
            font-style: normal;
            font-weight: 400;
            src:url("{{ public_path('fonts/NotoSansGurmukhi-Regular.ttf') }}") format("truetype");
        }
        @font-face {
            font-family: "NotoGurmukhi";
            font-style: normal;
            font-weight: 700;
            src:url("{{ public_path('fonts/NotoSansGurmukhi-Bold.ttf') }}") format("truetype");
        }
        @page { size: A4 portrait; margin: 12mm 13mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0; color: #000;
            font-family: "NotoGurmukhi", "DejaVu Sans", sans-serif;
            font-size: 12.2px; line-height: 1.58;
        }
        table, tr, td, th, div, span, strong, p {
            font-family: "NotoGurmukhi", "DejaVu Sans", sans-serif;
        }
        .no-print { text-align: center; margin: 10px 0 14px; }
        .no-print button { padding: 7px 14px; margin: 0 4px; border: 1px solid #444; background: #fff; }
        .page { width: 100%; margin: 0; padding: 0; }
        .right { text-align: right; }
        .university-title { font-size: 16px; font-weight: 700; text-align: center; margin-bottom: 7px; }
        .department-line { margin-top: 2px; }
        .meta-table, .endorsement-meta { width: 100%; border-collapse: collapse; margin-top: 7px; }
        .meta-table td, .endorsement-meta td { width: 50%; padding: 2px 0; vertical-align: top; }
        .order-title { text-align: center; font-size: 15px; font-weight: 700; text-decoration: underline; margin: 8px 0 7px; }
        .para { margin-top: 7px; text-align: justify; }
        .underline { display: inline-block; min-width: 90px; border-bottom: 1px solid #000; padding: 0 3px 1px; }
        .underline-sm { min-width: 55px; }
        .underline-md { min-width: 125px; }
        .underline-lg { min-width: 220px; }
        .sanction-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .sanction-table th, .sanction-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        .sanction-table th { text-align: center; font-weight: 700; line-height: 1.35; }
        .serial { width: 8%; text-align: center; }
        .description { width: 32%; }
        .quantity { width: 14%; text-align: center; }
        .purpose { width: 29%; }
        .amount { width: 17%; text-align: right; white-space: nowrap; }
        .total-row td { font-weight: 700; }
        .signature-row { width: 100%; border-collapse: collapse; margin-top: 26px; }
        .signature-row td { width: 50%; vertical-align: bottom; }
        .signature-box { text-align: center; font-weight: 700; padding-top: 34px; }
        .endorsement { margin-top: 22px; page-break-inside: avoid; }
        .copy-text { margin-top: 7px; }
        .copy-list { margin: 6px 0 0 22px; padding: 0; }
        .copy-list li { min-height: 17px; margin-bottom: 2px; }
        .system-note { margin-top: 8px; font-size: 9.8px; color: #333; border-top: 1px solid #777; padding-top: 4px; }
        @media print { .no-print { display: none; } body { margin: 0; } .page { page-break-inside: avoid; } }
    </style>
</head>
<body>
@if(empty($pdfMode))
    <div class="no-print">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>
@endif

@php
    $amount = $request->proforma_amount
        ?: optional($request->selectedEstimate)->estimate_amount
        ?: $request->financial_sanction_amount
        ?: 0;
    $amountText = $amount ? number_format((float) $amount, 2) : '____________';
    $vendorName = $request->selected_vendor_name ?: optional(optional($request->selectedEstimate)->vendor)->name;
    $employeeName = optional($request->employee)->display_name ?: '____________';
    $departmentName = optional($request->department)->name
        ?: optional(optional($request->employee)->department)->name
        ?: 'ਡਾਇਰੈਕਟੋਰੇਟ ਆਫ ਐਕਸਟੈਂਸ਼ਨ ਐਜੂਕੇਸ਼ਨ';
    $categoryName = optional($request->category)->name;
    $itemType = trim((string) ($request->item_type ?: ''));
    $itemName = trim((string) ($request->item_name ?: ''));
    $inventory = trim((string) ($request->inventory_no ?: ''));
    $room = trim((string) ($request->room_no ?: ''));

    $descriptionParts = [];
    if ($categoryName) $descriptionParts[] = $categoryName;
    if ($itemType && $itemType !== $categoryName) $descriptionParts[] = $itemType;
    if ($itemName) $descriptionParts[] = $itemName;
    $itemDescription = trim(implode(' - ', $descriptionParts));
    if (!$itemDescription) $itemDescription = $request->problem_description ?: 'ਲੋੜੀਂਦੀ ਵਸਤੂ / ਮੁਰੰਮਤ ਦਾ ਕੰਮ';

    $purposeText = $request->problem_description
        ?: $request->financial_sanction_purpose
        ?: 'ਦਫ਼ਤਰੀ ਵਰਤੋਂ ਅਤੇ ਲੋੜੀਂਦੀ ਮੁਰੰਮਤ/ਖਰੀਦ ਲਈ';

    $quantityText = isset($request->quantity) && $request->quantity ? $request->quantity : 1;
    $schemeName = $request->scheme_name ?: '____________________________';
    $schemeNumber = $request->scheme_number ?: '________________';
    $schemeCode = $request->scheme_code ?: '__________';
    $financialYear = $request->financial_year ?: '2026–27';
    $sanctionDate = optional($request->proforma_date ?: $request->created_at)->format('d-m-Y');
    $sanctionNumber = $request->sanction_number ?: $request->request_no ?: '';
    $delegationSerialNumber = $request->delegation_serial_number ?: '_______';
    $workType = $request->work_type ?: $request->purchase_payment_type ?: 'ਮੁਰੰਮਤ/ਖਰੀਦ/ਸੇਵਾ';
    $amountInWords = $request->amount_in_words ?: '____________________________';
    $endorsementNumber = $request->endorsement_number ?: '';
    $endorsementDate = optional($request->endorsement_date)->format('d-m-Y');
    $copyTo1 = $request->copy_to_1 ?: '';
    $copyTo2 = $request->copy_to_2 ?: '';
    $copyTo3 = $request->copy_to_3 ?: '';
@endphp

<div class="page">
    <div class="university-title">ਪੰਜਾਬ ਖੇਤੀਬਾੜੀ ਯੂਨੀਵਰਸਿਟੀ, ਲੁਧਿਆਣਾ।</div>

    <div class="department-line">
        <strong>ਵਿਭਾਗ:</strong>
        <span class="underline underline-lg">{{ $departmentName }}</span>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>ਨੰ:</strong> <span class="underline underline-md">{{ $sanctionNumber }}</span></td>
            <td class="right"><strong>ਮਿਤੀ:</strong> <span class="underline underline-md">{{ $sanctionDate }}</span></td>
        </tr>
    </table>

    <div class="order-title">ਹੁਕਮ</div>

    <div class="para">
        ਕੰਪਟਰੋਲਰ, ਪੰਜਾਬ ਖੇਤੀਬਾੜੀ ਯੂਨੀਵਰਸਿਟੀ, ਲੁਧਿਆਣਾ ਦੇ ਵਿੱਤੀ ਅਧਿਕਾਰਾਂ ਦੇ
        ਪ੍ਰਤੀਨਿਧੀਕਰਨ ਸੰਬੰਧੀ ਜਾਰੀ ਪੱਤਰ ਨੰ:
        <strong>CAU-B(1)/2025/20978-21076 ਮਿਤੀ 31.03.2025</strong>
        ਦੇ ਅਨੁਸਾਰ, ਲੜੀ ਨੰਬਰ
        <span class="underline underline-sm">{{ $delegationSerialNumber }}</span>
        ਅਧੀਨ ਰੁਪਏ
        <span class="underline underline-md">{{ $amountText }}</span>
        (ਅੰਕਾਂ ਵਿੱਚ) /
        <span class="underline underline-lg">{{ $amountInWords }}</span>
        (ਸ਼ਬਦਾਂ ਵਿੱਚ) ਦੀ ਰਕਮ
        <span class="underline underline-lg">{{ $workType }}</span>
        ਲਈ ਜਾਂ ਹੇਠਾਂ ਦਰਸਾਏ ਵੇਰਵਿਆਂ ਅਨੁਸਾਰ ਵਿੱਤੀ ਮਨਜ਼ੂਰੀ ਪ੍ਰਦਾਨ ਕੀਤੀ ਜਾਂਦੀ ਹੈ।
    </div>

    <table class="sanction-table">
        <thead>
            <tr>
                <th class="serial">ਲੜੀ ਨੰ:</th>
                <th class="description">ਵਸਤੂ/ਸੇਵਾ ਦਾ ਵੇਰਵਾ</th>
                <th class="quantity">ਮਾਤਰਾ/ ਵਿਵਸਥਾ</th>
                <th class="purpose">ਖਰੀਦ ਦਾ ਉਦੇਸ਼</th>
                <th class="amount">ਰੁਪਏ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="serial">1</td>
                <td class="description">
                    {{ $itemDescription }}
                    @if($inventory)<br><strong>ਇਨਵੈਂਟਰੀ ਨੰ:</strong> {{ $inventory }}@endif
                    @if($room)<br><strong>ਕਮਰਾ/ਸਥਾਨ:</strong> {{ $room }}@endif
                    @if($vendorName)<br><strong>ਫਰਮ:</strong> M/s {{ $vendorName }}@endif
                </td>
                <td class="quantity">{{ $quantityText }}</td>
                <td class="purpose">{{ $purposeText }}<br><strong>ਮੰਗਕਰਤਾ:</strong> {{ $employeeName }}</td>
                <td class="amount">{{ $amountText }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="right">ਕੁਲ ਜੋੜ</td>
                <td class="amount">{{ $amountText }}</td>
            </tr>
        </tbody>
    </table>

    <div class="para">
        ਉਪਰੋਕਤ ਰਕਮ ਵਿੱਤੀ ਸਾਲ <strong>{{ $financialYear }}</strong> ਦੌਰਾਨ ਸਕੀਮ ਦਾ ਨਾਮ
        <span class="underline underline-lg">{{ $schemeName }}</span>, ਸਕੀਮ ਨੰਬਰ
        <span class="underline underline-md">{{ $schemeNumber }}</span> ਅਤੇ ਕੋਡ
        <span class="underline">{{ $schemeCode }}</span> ਅਧੀਨ ਬੁੱਕ ਕੀਤੀ ਜਾਵੇਗੀ।
    </div>

    <div class="para">
        ਇਹ ਪ੍ਰਮਾਣਿਤ ਕੀਤਾ ਜਾਂਦਾ ਹੈ ਕਿ ਉਪਰੋਕਤ ਰਕਮ ਸੰਬੰਧਿਤ ਸਕੀਮ ਦੇ ਮੁੱਖ ਖੋਜਕਾਰ
        (PI)/ਸਹਿ-ਮੁੱਖ ਖੋਜਕਾਰ (Co-PI) ਵੱਲੋਂ ਲੋੜੀਂਦੇ ਖਰਚਿਆਂ ਦੇ ਭੁਗਤਾਨ ਲਈ ਜਾਰੀ
        ਕੀਤੀ ਜਾ ਰਹੀ ਹੈ। ਇਹ ਵੀ ਤਸਦੀਕ ਕੀਤਾ ਜਾਂਦਾ ਹੈ ਕਿ ਉਕਤ ਖਰਚਾ ਕੇਵਲ ਉਸੇ ਉਦੇਸ਼
        ਲਈ ਕੀਤਾ ਜਾ ਰਿਹਾ ਹੈ, ਜਿਸ ਲਈ ਸੰਬੰਧਿਤ ਫੰਡ ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ ਅਤੇ ਇਹ ਖਰਚਾ
        ਸਕੀਮ ਦੀਆਂ ਸ਼ਰਤਾਂ ਅਤੇ ਪ੍ਰਵਾਨਿਤ ਮਦਾਂ ਅਨੁਸਾਰ ਹੀ ਕੀਤਾ ਜਾ ਰਿਹਾ ਹੈ।
    </div>

    <table class="signature-row"><tr><td></td><td class="signature-box">ਡੀਨ/ਡਾਇਰੈਕਟਰ/ਵਿਭਾਗ ਦੇ ਮੁਖੀ ਦੇ ਹਸਤਾਖ਼ਰ</td></tr></table>

    <div class="endorsement">
        <table class="endorsement-meta">
            <tr>
                <td><strong>ਪਿੱਠ ਅੰਕਣ ਨੰ:</strong> <span class="underline underline-md">{{ $endorsementNumber }}</span></td>
                <td class="right"><strong>ਮਿਤੀ:</strong> <span class="underline underline-md">{{ $endorsementDate }}</span></td>
            </tr>
        </table>

        <div class="copy-text">
            ਉਪਰੋਕਤ ਦਾ ਉਤਾਰਾ ਹੇਠ ਲਿਖਿਆਂ ਨੂੰ ਸੂਚਨਾ ਅਤੇ ਲੋੜੀਂਦੀ ਕਾਰਵਾਈ ਹਿੱਤ ਭੇਜਿਆ ਜਾਂਦਾ ਹੈ। (ਜੇਕਰ ਲੋੜੀਂਦਾ ਹੈ)
        </div>

        <ol class="copy-list">
            <li>{{ $copyTo1 }}</li>
            <li>{{ $copyTo2 }}</li>
            <li>{{ $copyTo3 }}</li>
        </ol>

        <table class="signature-row"><tr><td></td><td class="signature-box">ਡੀਨ/ਡਾਇਰੈਕਟਰ/ਵਿਭਾਗ ਦੇ ਮੁਖੀ ਦੇ ਹਸਤਾਖ਼ਰ</td></tr></table>
    </div>

    <div class="system-note">
        ਸਿਸਟਮ ਰਿਕਾਰਡ: ਬੇਨਤੀ ਨੰ. {{ $request->request_no }}
        @if($categoryName) | ਸ਼੍ਰੇਣੀ: {{ $categoryName }} @endif
        | ਸਥਿਤੀ: {{ $request->status }} | ਪ੍ਰਿੰਟ ਮਿਤੀ: {{ date('d-m-Y') }}
    </div>
</div>
</body>
</html>
