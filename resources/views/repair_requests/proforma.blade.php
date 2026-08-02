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
            src: url("file://{{ public_path('fonts/NotoSansGurmukhi-Regular.ttf') }}");
        }

        @font-face {
            font-family: "NotoGurmukhi";
            font-style: normal;
            font-weight: 700;
            src: url("file://{{ public_path('fonts/NotoSansGurmukhi-Bold.ttf') }}");
        }

        @page {
            size: A4 portrait;
            margin: 15mm 18mm 13mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #000;
            font-family: "NotoGurmukhi", "DejaVu Sans", sans-serif;
            font-size: 13px;
            line-height: 1.72;
        }

        table, tr, td, th, div, span, strong, p, ol, li {
            font-family: "NotoGurmukhi", "DejaVu Sans", sans-serif;
        }

        .page {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .no-print {
            text-align: center;
            margin: 10px 0 15px;
        }

        .no-print button {
            padding: 7px 14px;
            margin: 0 4px;
        }

        .right {
            text-align: right;
        }

        .university-title {
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
            margin-top: 0;
        }

        .department-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            margin-top: 1px;
        }

        .meta-table,
        .endorsement-meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 9px;
        }

        .meta-table td,
        .endorsement-meta td {
            width: 50%;
            padding: 0;
            vertical-align: bottom;
        }

        .order-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
            margin: 7px 0 8px;
        }

        .para {
            margin-top: 7px;
            text-align: justify;
        }

        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 85px;
            min-height: 16px;
            padding: 0 3px 1px;
            vertical-align: baseline;
        }

        .u-xs { min-width: 45px; }
        .u-sm { min-width: 75px; }
        .u-md { min-width: 125px; }
        .u-lg { min-width: 200px; }

        .sanction-table-wrap {
            width: 88%;
            margin: 14px auto 0;
        }

        .sanction-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sanction-table th,
        .sanction-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: middle;
        }

        .sanction-table th {
            text-align: center;
            font-weight: 700;
            line-height: 1.35;
        }

        .sanction-table .serial {
            width: 10%;
            text-align: center;
        }

        .sanction-table .description {
            width: 26%;
        }

        .sanction-table .quantity {
            width: 20%;
            text-align: center;
        }

        .sanction-table .purpose {
            width: 28%;
        }

        .sanction-table .amount {
            width: 16%;
            text-align: right;
            white-space: nowrap;
        }

        .item-row td {
            height: 34px;
        }

        .total-row td {
            height: 25px;
            font-weight: 700;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 34px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: bottom;
        }

        .signature-text {
            text-align: center;
            font-weight: 700;
            padding-top: 33px;
        }

        .endorsement {
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .copy-text {
            margin-top: 9px;
            text-align: justify;
        }

        .copy-list {
            margin: 5px 0 0 24px;
            padding: 0;
        }

        .copy-list li {
            height: 21px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
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
    if (!function_exists('punjabiNumberWords')) {
        function punjabiNumberWords($number, $addRupees = true)
        {
            $number = (int) round($number);

            if ($number === 0) {
                return $addRupees ? 'ਸਿਫ਼ਰ ਰੁਪਏ ਮਾਤਰ' : 'ਸਿਫ਼ਰ';
            }

            $words = [
                0 => '',
                1 => 'ਇੱਕ',
                2 => 'ਦੋ',
                3 => 'ਤਿੰਨ',
                4 => 'ਚਾਰ',
                5 => 'ਪੰਜ',
                6 => 'ਛੇ',
                7 => 'ਸੱਤ',
                8 => 'ਅੱਠ',
                9 => 'ਨੌਂ',
                10 => 'ਦਸ',
                11 => 'ਗਿਆਰਾਂ',
                12 => 'ਬਾਰਾਂ',
                13 => 'ਤੇਰਾਂ',
                14 => 'ਚੌਦਾਂ',
                15 => 'ਪੰਦਰਾਂ',
                16 => 'ਸੋਲਾਂ',
                17 => 'ਸਤਾਰਾਂ',
                18 => 'ਅਠਾਰਾਂ',
                19 => 'ਉੱਨੀ',
                20 => 'ਵੀਹ',
                21 => 'ਇੱਕੀ',
                22 => 'ਬਾਈ',
                23 => 'ਤੇਈ',
                24 => 'ਚੌਵੀ',
                25 => 'ਪੱਚੀ',
                26 => 'ਛੱਬੀ',
                27 => 'ਸਤਾਈ',
                28 => 'ਅਠਾਈ',
                29 => 'ਉਨੱਤੀ',
                30 => 'ਤੀਹ',
                31 => 'ਇਕੱਤੀ',
                32 => 'ਬੱਤੀ',
                33 => 'ਤੇਤੀ',
                34 => 'ਚੌਂਤੀ',
                35 => 'ਪੈਂਤੀ',
                36 => 'ਛੱਤੀ',
                37 => 'ਸੈਂਤੀ',
                38 => 'ਅਠੱਤੀ',
                39 => 'ਉਨਤਾਲੀ',
                40 => 'ਚਾਲੀ',
                41 => 'ਇਕਤਾਲੀ',
                42 => 'ਬਿਆਲੀ',
                43 => 'ਤੇਤਾਲੀ',
                44 => 'ਚੁਤਾਲੀ',
                45 => 'ਪੰਤਾਲੀ',
                46 => 'ਛਿਆਲੀ',
                47 => 'ਸੰਤਾਲੀ',
                48 => 'ਅਠਤਾਲੀ',
                49 => 'ਉਨੰਜਾ',
                50 => 'ਪੰਜਾਹ',
                51 => 'ਇਕਵੰਜਾ',
                52 => 'ਬਵੰਜਾ',
                53 => 'ਤਰਵੰਜਾ',
                54 => 'ਚੁਰੰਜਾ',
                55 => 'ਪਚਵੰਜਾ',
                56 => 'ਛਪੰਜਾ',
                57 => 'ਸਤਵੰਜਾ',
                58 => 'ਅਠਵੰਜਾ',
                59 => 'ਉਨਾਹਠ',
                60 => 'ਸੱਠ',
                61 => 'ਇਕਾਹਠ',
                62 => 'ਬਾਹਠ',
                63 => 'ਤਰੇਹਠ',
                64 => 'ਚੌਂਹਠ',
                65 => 'ਪੈਂਹਠ',
                66 => 'ਛਿਆਹਠ',
                67 => 'ਸਤਾਹਠ',
                68 => 'ਅਠਾਹਠ',
                69 => 'ਉਨੱਤਰ',
                70 => 'ਸੱਤਰ',
                71 => 'ਇਕਹੱਤਰ',
                72 => 'ਬਹੱਤਰ',
                73 => 'ਤਿਹੱਤਰ',
                74 => 'ਚੁਹੱਤਰ',
                75 => 'ਪਝੱਤਰ',
                76 => 'ਛਿਹੱਤਰ',
                77 => 'ਸਤੱਤਰ',
                78 => 'ਅਠੱਤਰ',
                79 => 'ਉਨਾਸੀ',
                80 => 'ਅੱਸੀ',
                81 => 'ਇਕਿਆਸੀ',
                82 => 'ਬਿਆਸੀ',
                83 => 'ਤਰਿਆਸੀ',
                84 => 'ਚੌਰਾਸੀ',
                85 => 'ਪਚਾਸੀ',
                86 => 'ਛਿਆਸੀ',
                87 => 'ਸਤਾਸੀ',
                88 => 'ਅਠਾਸੀ',
                89 => 'ਨਵਾਸੀ',
                90 => 'ਨੱਬੇ',
                91 => 'ਇਕਾਨਵੇਂ',
                92 => 'ਬਾਨਵੇਂ',
                93 => 'ਤਰਾਨਵੇਂ',
                94 => 'ਚੁਰਾਨਵੇਂ',
                95 => 'ਪਚਾਨਵੇਂ',
                96 => 'ਛਿਆਨਵੇਂ',
                97 => 'ਸਤਾਨਵੇਂ',
                98 => 'ਅਠਾਨਵੇਂ',
                99 => 'ਨੜਿਨਵੇਂ',
            ];

            $parts = [];

            if ($number >= 10000000) {
                $crore = intdiv($number, 10000000);
                $parts[] = punjabiNumberWords($crore, false).' ਕਰੋੜ';
                $number %= 10000000;
            }

            if ($number >= 100000) {
                $lakh = intdiv($number, 100000);
                $parts[] = punjabiNumberWords($lakh, false).' ਲੱਖ';
                $number %= 100000;
            }

            if ($number >= 1000) {
                $thousand = intdiv($number, 1000);
                $parts[] = punjabiNumberWords($thousand, false).' ਹਜ਼ਾਰ';
                $number %= 1000;
            }

            if ($number >= 100) {
                $hundred = intdiv($number, 100);
                $parts[] = $words[$hundred].' ਸੌ';
                $number %= 100;
            }

            if ($number > 0) {
                $parts[] = $words[$number];
            }

            $result = trim(implode(' ', $parts));

            return $addRupees
                ? $result.' ਰੁਪਏ ਮਾਤਰ'
                : $result;
        }
    }

    $amount = $request->proforma_amount
        ?: optional($request->selectedEstimate)->estimate_amount
        ?: $request->financial_sanction_amount
        ?: 0;

    $amountText = $amount
        ? number_format((float) $amount, 2)
        : '';

    $amountInWords = $request->amount_in_words
        ?: punjabiNumberWords($amount);

    $employeeName = optional($request->employee)->display_name ?: '';

    $departmentName = 'ਨਿਰਦੇਸ਼ਕ ਪਸਾਰ ਸਿੱਖਿਆ';

    $categoryName = optional($request->category)->name;
    $itemType = trim((string) ($request->item_type ?: ''));
    $itemName = trim((string) ($request->item_name ?: ''));
    $inventory = trim((string) ($request->inventory_no ?: ''));

    $descriptionParts = [];
    if ($categoryName) {
        $descriptionParts[] = $categoryName;
    }
    if ($itemType && $itemType !== $categoryName) {
        $descriptionParts[] = $itemType;
    }
    if ($itemName) {
        $descriptionParts[] = $itemName;
    }

    $itemDescription = trim(implode(' - ', $descriptionParts));

    $purposeText = $request->problem_description
        ?: $request->financial_sanction_purpose
        ?: '';

    $quantityText = isset($request->quantity) && $request->quantity
        ? $request->quantity
        : '';

    $financialYear = $request->financial_year ?: '2026–27';
    $schemeName = $request->scheme_name ?: '';
    $schemeNumber = $request->scheme_number ?: '';
    $schemeCode = $request->scheme_code ?: '';

    $sanctionNumber = $request->sanction_number
        ?: $request->request_no
        ?: '';

    $sanctionDate = optional(
        $request->proforma_date ?: $request->created_at
    )->format('d-m-Y');

    $delegationSerialNumber = $request->delegation_serial_number ?: '';

    $workTypeEnglish = $request->work_type
        ?: $request->purchase_payment_type
        ?: '';

    $workTypeMap = [
        'purchase' => 'ਖਰੀਦ',
        'repair' => 'ਮੁਰੰਮਤ',
        'service' => 'ਸੇਵਾ',
        'material' => 'ਸਮੱਗਰੀ ਦੀ ਖਰੀਦ',
        'purchase / payment of material / repair'
            => 'ਸਮੱਗਰੀ ਦੀ ਖਰੀਦ/ਭੁਗਤਾਨ/ਮੁਰੰਮਤ',
        'repair / purchase / payment of material'
            => 'ਮੁਰੰਮਤ/ਖਰੀਦ/ਸਮੱਗਰੀ ਦਾ ਭੁਗਤਾਨ',
    ];

    $workTypeKey = strtolower(trim($workTypeEnglish));

    $workType = isset($workTypeMap[$workTypeKey])
        ? $workTypeMap[$workTypeKey]
        : ($workTypeEnglish ?: 'ਮੁਰੰਮਤ/ਖਰੀਦ/ਸੇਵਾ');

    $endorsementNumber = $request->endorsement_number ?: '';
    $endorsementDate = optional($request->endorsement_date)->format('d-m-Y');

    $copyTo1 = $request->copy_to_1 ?: '';
    $copyTo2 = $request->copy_to_2 ?: '';
    $copyTo3 = $request->copy_to_3 ?: '';
@endphp

<div class="page">

    <div class="university-title">
        ਪੰਜਾਬ ਖੇਤੀਬਾੜੀ ਯੂਨੀਵਰਸਿਟੀ, ਲੁਧਿਆਣਾ।
    </div>

    <div class="department-title">
        ਵਿਭਾਗ:
        <span class="underline u-lg">{{ $departmentName }}</span>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                ਨੰ:
                <span class="underline u-md">{{ $sanctionNumber }}</span>
            </td>

            <td class="right">
                ਮਿਤੀ:
                <span class="underline u-md">{{ $sanctionDate }}</span>
            </td>
        </tr>
    </table>

    <div class="order-title">ਹੁਕਮ</div>

    <div class="para">
        ਕੰਪਟਰੋਲਰ, ਪੰਜਾਬ ਖੇਤੀਬਾੜੀ ਯੂਨੀਵਰਸਿਟੀ, ਲੁਧਿਆਣਾ ਦੇ ਵਿੱਤੀ ਅਧਿਕਾਰਾਂ ਦੇ
        ਪ੍ਰਤੀਨਿਧੀਕਰਨ ਸੰਬੰਧੀ ਜਾਰੀ ਪੱਤਰ ਨੰ:
        <strong>CAU-B(1)/2025/20978-21076 ਮਿਤੀ 31.03.2025</strong>
        ਦੇ ਅਨੁਸਾਰ, ਲੜੀ ਨੰਬਰ
        <span class="underline u-sm">{{ $delegationSerialNumber }}</span>
        ਅਧੀਨ ਰੁਪਏ
        <span class="underline u-md">{{ $amountText }}</span>
        (ਅੰਕਾਂ ਵਿੱਚ) /
        <span class="underline u-lg">{{ $amountInWords }}</span>
        (ਸ਼ਬਦਾਂ ਵਿੱਚ) ਦੀ ਰਕਮ
        <span class="underline u-lg">{{ $workType }}</span>
        (ਕੰਮ/ਖਰੀਦ/ਸੇਵਾ ਦੀ ਕਿਸਮ) ਲਈ ਜਾਂ ਹੇਠਾਂ ਦਰਸਾਏ ਵੇਰਵਿਆਂ ਅਨੁਸਾਰ
        ਵਿੱਤੀ ਮਨਜ਼ੂਰੀ ਪ੍ਰਦਾਨ ਕੀਤੀ ਜਾਂਦੀ ਹੈ।
    </div>

    <div class="sanction-table-wrap">
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
                <tr class="item-row">
                    <td class="serial">1</td>

                    <td class="description">
                        {{ $itemDescription }}
                        @if($inventory)
                            <br>ਇਨਵੈਂਟਰੀ ਨੰ: {{ $inventory }}
                        @endif
                    </td>

                    <td class="quantity">
                        {{ $quantityText }}
                    </td>

                    <td class="purpose">
                        {{ $purposeText }}
                        @if($employeeName)
                            <br>ਮੰਗਕਰਤਾ: {{ $employeeName }}
                        @endif
                    </td>

                    <td class="amount">
                        {{ $amountText }}
                    </td>
                </tr>

                <tr class="total-row">
                    <td colspan="4" class="right">ਕੁਲ ਜੋੜ</td>
                    <td class="amount">{{ $amountText }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="para" style="margin-top: 22px;">
        ਉਪਰੋਕਤ ਰਕਮ ਵਿੱਤੀ ਸਾਲ
        <strong>{{ $financialYear }}</strong>
        ਦੌਰਾਨ ਸਕੀਮ ਦਾ ਨਾਮ
        <span class="underline u-lg">{{ $schemeName }}</span>,
        ਸਕੀਮ ਨੰਬਰ
        <span class="underline u-md">{{ $schemeNumber }}</span>
        ਅਤੇ ਕੋਡ
        <span class="underline u-sm">{{ $schemeCode }}</span>
        ਅਧੀਨ ਬੁੱਕ ਕੀਤੀ ਜਾਵੇਗੀ।
    </div>

    <div class="para" style="margin-top: 15px;">
        ਇਹ ਪ੍ਰਮਾਣਿਤ ਕੀਤਾ ਜਾਂਦਾ ਹੈ ਕਿ ਉਪਰੋਕਤ ਰਕਮ ਸੰਬੰਧਿਤ ਸਕੀਮ ਦੇ ਮੁੱਖ ਖੋਜਕਾਰ
        (PI)/ਸਹਿ-ਮੁੱਖ ਖੋਜਕਾਰ (Co-PI) ਵੱਲੋਂ ਲੋੜੀਂਦੇ ਖਰਚਿਆਂ ਦੇ ਭੁਗਤਾਨ ਲਈ ਜਾਰੀ
        ਕੀਤੀ ਜਾ ਰਹੀ ਹੈ। ਇਹ ਵੀ ਤਸਦੀਕ ਕੀਤਾ ਜਾਂਦਾ ਹੈ ਕਿ ਉਕਤ ਖਰਚਾ ਕੇਵਲ ਉਸੇ
        ਉਦੇਸ਼ ਲਈ ਕੀਤਾ ਜਾ ਰਿਹਾ ਹੈ, ਜਿਸ ਲਈ ਸੰਬੰਧਿਤ ਫੰਡ ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ ਅਤੇ ਇਹ
        ਖਰਚਾ ਸਕੀਮ ਦੀਆਂ ਸ਼ਰਤਾਂ ਅਤੇ ਪ੍ਰਵਾਨਿਤ ਮਦਾਂ ਅਨੁਸਾਰ ਹੀ ਕੀਤਾ ਜਾ ਰਿਹਾ ਹੈ।
    </div>

    <table class="signature-table">
        <tr>
            <td></td>
            <td class="signature-text">
                ਡੀਨ/ਡਾਇਰੈਕਟਰ/ਵਿਭਾਗ ਦੇ ਮੁਖੀ ਦੇ ਹਸਤਾਖ਼ਰ
            </td>
        </tr>
    </table>

    <div class="endorsement">
        <table class="endorsement-meta">
            <tr>
                <td>
                    ਪਿੱਠ ਅੰਕਣ ਨੰ:
                    <span class="underline u-md">{{ $endorsementNumber }}</span>
                </td>

                <td class="right">
                    ਮਿਤੀ:
                    <span class="underline u-md">{{ $endorsementDate }}</span>
                </td>
            </tr>
        </table>

        <div class="copy-text">
            ਉਪਰੋਕਤ ਦਾ ਉਤਾਰਾ ਹੇਠ ਲਿਖਿਆਂ ਨੂੰ ਸੂਚਨਾ ਅਤੇ ਲੋੜੀਂਦੀ ਕਾਰਵਾਈ ਹਿੱਤ
            ਭੇਜਿਆ ਜਾਂਦਾ ਹੈ। (ਜੇਕਰ ਲੋੜੀਂਦਾ ਹੈ)
        </div>

        <ol class="copy-list">
            <li>{{ $copyTo1 }}</li>
            <li>{{ $copyTo2 }}</li>
            <li>{{ $copyTo3 }}</li>
        </ol>

        <table class="signature-table" style="margin-top: 18px;">
            <tr>
                <td></td>
                <td class="signature-text">
                    ਡੀਨ/ਡਾਇਰੈਕਟਰ/ਵਿਭਾਗ ਦੇ ਮੁਖੀ ਦੇ ਹਸਤਾਖ਼ਰ
                </td>
            </tr>
        </table>
    </div>
   <div class="system-note">
        ਸਿਸਟਮ ਰਿਕਾਰਡ: ਬੇਨਤੀ ਨੰ. {{ $request->request_no }}
        @if($categoryName) | ਸ਼੍ਰੇਣੀ: {{ $categoryName }} @endif
        | ਸਥਿਤੀ: {{ $request->status }} | ਪ੍ਰਿੰਟ ਮਿਤੀ: {{ date('d-m-Y') }}
    </div>
</div>

</body>
</html>