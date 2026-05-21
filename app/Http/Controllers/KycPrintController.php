<?php

namespace App\Http\Controllers;

use App\Models\KycProfile;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class KycPrintController extends Controller
{
    public function print(KycProfile $kyc)
    {
        // تحميل العلاقات
        $kyc->load(['user']);

        // إعداد mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'margin_top' => 10,
            'margin_bottom' => 15,
        ]);

// ✅ مهم جدًا للعربية
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont  = true;
        $mpdf->useSubstitutions = false;

// اجعل الاتجاه الافتراضي LTR (حتى لا تنقلب الجداول)
        $mpdf->SetDirectionality('ltr');



        // توليد HTML من Blade
        $html = view('pdf.kyc-form', [
            'kyc'  => $kyc,
            'date' => now()->format('Y-m-d'),
        ])->render();

        // كتابة المحتوى
        $mpdf->WriteHTML($html);

        // تحميل مباشر
        return response($mpdf->Output("KYC-{$kyc->id}.pdf", 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="KYC-'.$kyc->id.'.pdf"',
        ]);
    }
}
