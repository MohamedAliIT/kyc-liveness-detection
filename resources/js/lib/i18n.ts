export type Lang = "en" | "ar";

export const dict = {
    en: {
        kycTitle: "KYC Form (Individuals)",
        step: "Step",
        next: "Next",
        back: "Back",
        save: "Save",
        submit: "Submit KYC",
        draft: "Draft",
        submitted: "Submitted",
        verified: "Verified",
        rejected: "Rejected",
        officeUseOnly: "For Office Use Only",
        verifiedBy: "Verified By",
        dateVerified: "Date Verified",
        remarks: "Remarks",

        // Steps
        s1: "Personal Information",
        s2: "Identification Details",
        s3: "Source of Funds / Income",
        s4: "Politically Exposed Person (PEP)",
        s5: "Beneficial Ownership / UBO",
        s6: "Declaration",

        // Fields
        fullName: "Full Name",
        dob: "Date of Birth",
        nationality: "Nationality",
        occupation: "Occupation",
        employer: "Employer or Business Name",
        phone: "Contact Number",
        email: "Email Address",
        address: "Residential Address",

        idNumber: "ID Number",
        idIssue: "Date of Issue",
        idExpiry: "Expiry Date",
        issuingAuthority: "Issuing Authority",

        incomeSource: "Source of Income",
        incomeRange: "Monthly/Annual Income",
        purpose: "Purpose of Relationship",

        pepQ: "Are you or a family member a PEP?",
        yes: "Yes",
        no: "No",
        pepDetails: "If yes, please specify",

        behalfQ: "Are you acting on behalf of another person?",
        uboDetails: "If yes, please specify",

        declarationText:
            "I hereby declare that the above information is true and correct to the best of my knowledge, and I agree to inform Liberal Lawyers of any changes.",
        accept: "I accept the declaration",

        incomplete: "Please complete required fields.",
    },
    ar: {
        kycTitle: "نموذج اعرف عميلك للأفراد",
        step: "الخطوة",
        next: "التالي",
        back: "السابق",
        save: "حفظ",
        submit: "إرسال نموذج KYC",
        draft: "مسودة",
        submitted: "تم الإرسال",
        verified: "تم التحقق",
        rejected: "مرفوض",
        officeUseOnly: "للمعنيون بالأمر فقط",
        verifiedBy: "تم التحقق من قبل",
        dateVerified: "تاريخ التحقق",
        remarks: "ملاحظات",

        s1: "المعلومات الشخصية",
        s2: "تفاصيل الهوية",
        s3: "مصدر الأموال / الدخل",
        s4: "شخص مكشوف سياسيًا (PEP)",
        s5: "المالك المستفيد / المستفيد النهائي",
        s6: "إقرار",

        fullName: "الاسم الكامل",
        dob: "تاريخ الميلاد",
        nationality: "الجنسية",
        occupation: "المهنة",
        employer: "اسم جهة العمل أو العمل",
        phone: "رقم الهاتف",
        email: "البريد الإلكتروني",
        address: "عنوان السكن",

        idNumber: "رقم الهوية",
        idIssue: "تاريخ الإصدار",
        idExpiry: "تاريخ الانتهاء",
        issuingAuthority: "جهة الإصدار",

        incomeSource: "مصدر الدخل",
        incomeRange: "الدخل الشهري/السنوي",
        purpose: "الغرض من العلاقة",

        pepQ: "هل أنت أو أحد أفراد أسرتك شخص مكشوف سياسيًا؟",
        yes: "نعم",
        no: "لا",
        pepDetails: "إذا كانت الإجابة نعم يرجى التوضيح",

        behalfQ: "هل تتصرف نيابة عن شخص آخر؟",
        uboDetails: "إذا كانت الإجابة نعم يرجى التوضيح",

        declarationText:
            "أقر بأن جميع المعلومات أعلاه صحيحة حسب علمي، وأتعهد بإبلاغ شركة ليبرال لويرز بأي تغييرات تطرأ عليها.",
        accept: "أوافق على الإقرار",

        incomplete: "يرجى إكمال الحقول المطلوبة.",
    },
} as const;

export function t(lang: Lang, key: keyof typeof dict.en) {
    return dict[lang][key] as string;
}
