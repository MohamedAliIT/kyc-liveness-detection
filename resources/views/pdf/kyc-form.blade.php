<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            direction: ltr;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 14px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* Column sizing */
        .col-en-label { width: 22%; font-weight: bold; }
        .col-en-value { width: 28%; }
        .col-ar-value { width: 28%; }
        .col-ar-label { width: 22%; font-weight: bold; }

        /* Direction helpers */
        .td-ar {
            direction: rtl;
            text-align: right;
        }

        .td-ltr {
            direction: ltr;
            text-align: left;
        }

        /* Section headers */
        .section {
            background: #f2f2f2;
            font-weight: bold;
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .office {
            background: #ffe0c1;
            font-weight: bold;
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo {
            height: 55px;
            margin-bottom: 6px;
        }

        .title {
            border: 2px solid #000;
            padding: 8px;
            font-weight: bold;
            margin-bottom: 12px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .muted {
            color: #444;
            font-weight: normal;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="header">
    <img src="{{ public_path('logo/logo.png') }}" class="logo">

    <table style="width:100%; border:none; margin-top:5px;">
        <tr>
            <td style="border:none; text-align:left; direction:ltr;" class="muted">
                LIBERAL LAWYERS
            </td>

            <td style="border:none; text-align:right; direction:rtl; font-weight:bold;">
                المحامون الأحرار
            </td>
        </tr>
    </table>
</div>


{{-- ================= TITLE ================= --}}
<div class="title">
    KYC Form for Individuals — نموذج اعرف عميلك للأفراد
</div>

{{-- =========================
     1. PERSONAL INFORMATION
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">1. Personal Information</td>
        <td colspan="2" class="section td-ar">1. المعلومات الشخصية</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Full Name</td>
        <td class="col-en-value td-ltr">{{ $kyc->full_name_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->full_name_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">الاسم الكامل</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Date of Birth</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->date_of_birth ?? '—' }}</td>
        <td class="col-ar-label td-ar">تاريخ الميلاد</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Nationality</td>
        <td class="col-en-value td-ltr">{{ $kyc->nationality_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->nationality_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">الجنسية</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Occupation</td>
        <td class="col-en-value td-ltr">{{ $kyc->occupation_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->occupation_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">المهنة</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Employer / Business</td>
        <td class="col-en-value td-ltr">{{ $kyc->employer_or_business_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->employer_or_business_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">جهة العمل</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Contact Number</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->contact_number ?? '—' }}</td>
        <td class="col-ar-label td-ar">رقم الهاتف</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Email</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->email_address ?? '—' }}</td>
        <td class="col-ar-label td-ar">البريد الإلكتروني</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Residential Address</td>
        <td class="col-en-value td-ltr">{{ $kyc->residential_address_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->residential_address_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">عنوان السكن</td>
    </tr>
</table>

{{-- =========================
     2. IDENTIFICATION DETAILS
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">2. Identification Details</td>
        <td colspan="2" class="section td-ar">2. تفاصيل الهوية</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">ID Number</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->id_number ?? '—' }}</td>
        <td class="col-ar-label td-ar">رقم الهوية</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Issuing Authority</td>
        <td class="col-en-value td-ltr">{{ $kyc->issuing_authority_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->issuing_authority_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">جهة الإصدار</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Issue Date</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->id_issue_date ?? '—' }}</td>
        <td class="col-ar-label td-ar">تاريخ الإصدار</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Expiry Date</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->id_expiry_date ?? '—' }}</td>
        <td class="col-ar-label td-ar">تاريخ الانتهاء</td>
    </tr>
</table>

{{-- =========================
     3. SOURCE OF FUNDS / INCOME
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">3. Source of Funds / Income</td>
        <td colspan="2" class="section td-ar">3. مصدر الأموال / الدخل</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Source of Income</td>
        <td class="col-en-value td-ltr">{{ $kyc->source_of_income_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->source_of_income_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">مصدر الدخل</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Income Range</td>
        <td colspan="2" style="text-align:center;">{{ $kyc->income_range ?? '—' }}</td>
        <td class="col-ar-label td-ar">نطاق الدخل</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Purpose of Relationship</td>
        <td class="col-en-value td-ltr">{{ $kyc->purpose_of_relationship_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->purpose_of_relationship_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">الغرض من العلاقة</td>
    </tr>
</table>

{{-- =========================
     4. PEP
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">4. PEP</td>
        <td colspan="2" class="section td-ar">4. شخص مكشوف سياسياً</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Is PEP?</td>
        <td class="col-en-value td-ltr">{{ $kyc->is_pep ? 'Yes' : 'No' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->is_pep ? 'نعم' : 'لا' }}</td>
        <td class="col-ar-label td-ar">هل هو PEP؟</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">PEP Details</td>
        <td class="col-en-value td-ltr">{{ $kyc->pep_details_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->pep_details_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">تفاصيل PEP</td>
    </tr>
</table>

{{-- =========================
     5. UBO
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">5. UBO</td>
        <td colspan="2" class="section td-ar">5. المستفيد النهائي</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">Acting on behalf?</td>
        <td class="col-en-value td-ltr">{{ $kyc->acting_on_behalf ? 'Yes' : 'No' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->acting_on_behalf ? 'نعم' : 'لا' }}</td>
        <td class="col-ar-label td-ar">نيابة عن شخص آخر؟</td>
    </tr>

    <tr>
        <td class="col-en-label td-ltr">UBO Details</td>
        <td class="col-en-value td-ltr">{{ $kyc->ubo_details_en ?? '—' }}</td>
        <td class="col-ar-value td-ar">{{ $kyc->ubo_details_ar ?? '—' }}</td>
        <td class="col-ar-label td-ar">تفاصيل المستفيد النهائي</td>
    </tr>
</table>

{{-- =========================
     6. DECLARATION
========================= --}}
<table>
    <tr>
        <td colspan="2" class="section td-ltr">6. Declaration</td>
        <td colspan="2" class="section td-ar">6. الإقرار</td>


    </tr>

    <tr>
        <td colspan="4" class="td-ar">
            أقر بأن جميع المعلومات أعلاه صحيحة وأتعهد بإبلاغ مكتب المحامون الأحرار بأي تغييرات.
            <br><br>
            <span class="td-ltr">
                I hereby declare that the above information is true and correct to the best of my knowledge and I agree to inform Liberal Lawyers of any changes.
            </span>
        </td>
    </tr>

    <tr>
        <td class="col-ar-label td-ar">التوقيع</td>
        <td class="col-en-value td-ltr" colspan="3"></td>
    </tr>

    <tr>
        <td class="col-ar-label td-ar">التاريخ</td>
        <td class="col-en-value td-ltr" colspan="3">
            {{ $date ?? now()->format('Y-m-d') }}
        </td>
    </tr>
</table>

{{-- =========================
     OFFICE USE ONLY
========================= --}}
<table>
    <tr>
        <td colspan="2" class="office td-ltr">For Office Use Only</td>
        <td colspan="2" class="office td-ar">للاستخدام الإداري فقط</td>
    </tr>

    {{-- VERIFIED BY --}}
    <tr>
        <td class="col-en-label td-ltr">Verified By</td>
        <td colspan="2" style="text-align:center; font-weight:bold;">
            {{ optional($kyc->verifier)->name ?? '—' }}
        </td>

        <td class="col-ar-label td-ar">تم التحقق بواسطة</td>
    </tr>

    {{-- DATE VERIFIED (CENTERED SINGLE VALUE) --}}
    <tr>
        <td class="col-en-label td-ltr">Date Verified</td>

        <td colspan="2" style="text-align:center; font-weight:bold;">
            {{ $kyc->verified_at
                ? \Carbon\Carbon::parse($kyc->verified_at)->format('Y-m-d')
                : '—'
            }}
        </td>

        <td class="col-ar-label td-ar">تاريخ التحقق</td>
    </tr>

    {{-- REMARKS --}}
    <tr>
        <td class="col-en-label td-ltr">Remarks</td>
        <td colspan="2" style="text-align:center; font-weight:bold;">{{ $kyc->office_remarks ?? '—' }}</td>
        <td class="col-ar-label td-ar">ملاحظات</td>
    </tr>
</table>


{{-- ================= FOOTER ================= --}}
<htmlpagefooter name="mainFooter">
    <div style="text-align:center; font-size:11px; border-top:1px solid #ccc; padding-top:5px;">
        LIBERAL LAWYERS — KYC Document — Generated {{ now()->format('Y-m-d H:i') }}
    </div>
</htmlpagefooter>
<sethtmlpagefooter name="mainFooter" value="on" />

</body>
</html>
