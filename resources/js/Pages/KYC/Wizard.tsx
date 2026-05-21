import React, { useEffect, useMemo, useState } from "react";
import { useForm } from "@inertiajs/react";
import type { Lang } from "@/lib/i18n";
import { t } from "@/lib/i18n";

// IMPORTANT: if your project uses Ziggy route() helper:
declare function route(name: string, params?: any): string;

/* =========================================================
 * Types
 * ========================================================= */
type KycProfile = {
    id: number;
    user_id: number;
    current_step: number;
    status:
        | "draft"
        | "submitted"
        | "verified"
        | "face_required"
        | "face_enrolled"
        | "active"
        | "rejected";

    submitted_at?: string | null;

    full_name_ar?: string | null;
    full_name_en?: string | null;
    date_of_birth?: string | null;

    nationality_ar?: string | null;
    nationality_en?: string | null;

    occupation_ar?: string | null;
    occupation_en?: string | null;

    employer_or_business_ar?: string | null;
    employer_or_business_en?: string | null;

    contact_number?: string | null;
    email_address?: string | null;

    residential_address_ar?: string | null;
    residential_address_en?: string | null;

    id_number?: string | null;
    issuing_authority_ar?: string | null;
    issuing_authority_en?: string | null;
    id_issue_date?: string | null;
    id_expiry_date?: string | null;

    source_of_income_ar?: string | null;
    source_of_income_en?: string | null;
    income_range?: string | null;

    purpose_of_relationship_ar?: string | null;
    purpose_of_relationship_en?: string | null;

    is_pep?: boolean | null;
    pep_details_ar?: string | null;
    pep_details_en?: string | null;

    acting_on_behalf?: boolean | null;
    ubo_details_ar?: string | null;
    ubo_details_en?: string | null;

    declaration_accepted?: boolean;

    verified_by?: number | null;
    verified_at?: string | null;
    office_remarks?: string | null;
    rejection_reason?: string | null;
};

type UserPayload = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
};

/* =========================================================
 * Helpers
 * ========================================================= */
function cx(...classes: Array<string | false | null | undefined>) {
    return classes.filter(Boolean).join(" ");
}

/* =========================================================
 * UI Components
 * ========================================================= */

function PageShell({
                       dir,
                       children,
                   }: {
    dir: "rtl" | "ltr";
    children: React.ReactNode;
}) {
    return (
        <div dir={dir} className="min-h-screen bg-slate-50">
            <div className="max-w-6xl mx-auto px-4 py-8">{children}</div>
        </div>
    );
}

function Card({
                  title,
                  right,
                  children,
              }: {
    title: React.ReactNode;
    right?: React.ReactNode;
    children: React.ReactNode;
}) {
    return (
        <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div className="px-6 py-5 border-b border-slate-200 flex items-start justify-between gap-4">
                <div className="text-slate-900 font-black text-lg">{title}</div>
                {right && <div>{right}</div>}
            </div>
            <div className="p-6">{children}</div>
        </div>
    );
}

function StatusBadge({
                         status,
                         label,
                     }: {
    status: KycProfile["status"];
    label: string;
}) {
    const cls =
        status === "draft"
            ? "bg-slate-100 text-slate-700 border-slate-200"
            : status === "submitted"
                ? "bg-amber-100 text-amber-800 border-amber-200"
                : status === "active" || status === "verified"
                    ? "bg-emerald-100 text-emerald-800 border-emerald-200"
                    : status === "face_required" || status === "face_enrolled"
                        ? "bg-indigo-100 text-indigo-800 border-indigo-200"
                        : "bg-rose-100 text-rose-800 border-rose-200";

    return (
        <span className={cx("px-3 py-1 rounded-full text-xs font-extrabold border", cls)}>
      {label}
    </span>
    );
}

function TextInput({
                       value,
                       onChange,
                       type = "text",
                       disabled,
                   }: {
    value: string;
    onChange: (v: string) => void;
    type?: string;
    disabled?: boolean;
}) {
    return (
        <input
            type={type}
            value={value}
            disabled={disabled}
            onChange={(e) => onChange(e.target.value)}
            className={cx(
                "w-full px-4 py-3 rounded-xl border border-slate-200",
                "focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300",
                disabled ? "bg-slate-50 cursor-not-allowed" : "bg-white"
            )}
        />
    );
}

function TextAreaInput({
                           value,
                           onChange,
                           disabled,
                       }: {
    value: string;
    onChange: (v: string) => void;
    disabled?: boolean;
}) {
    return (
        <textarea
            value={value}
            disabled={disabled}
            onChange={(e) => onChange(e.target.value)}
            className={cx(
                "w-full px-4 py-3 rounded-xl border border-slate-200 min-h-[120px]",
                "focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300",
                disabled ? "bg-slate-50 cursor-not-allowed" : "bg-white"
            )}
        />
    );
}

function BoolRadio({
                       name,
                       value,
                       onChange,
                       disabled,
                       yesLabel,
                       noLabel,
                   }: {
    name: string;
    value: boolean;
    onChange: (v: boolean) => void;
    disabled?: boolean;
    yesLabel: string;
    noLabel: string;
}) {
    return (
        <div className="flex items-center gap-6">
            <label className="flex items-center gap-2 font-bold">
                <input
                    type="radio"
                    name={name}
                    checked={value === true}
                    disabled={disabled}
                    onChange={() => onChange(true)}
                />
                {yesLabel}
            </label>

            <label className="flex items-center gap-2 font-bold">
                <input
                    type="radio"
                    name={name}
                    checked={value === false}
                    disabled={disabled}
                    onChange={() => onChange(false)}
                />
                {noLabel}
            </label>
        </div>
    );
}

function SingleField({
                         label,
                         children,
                         error,
                     }: {
    label: string;
    children: React.ReactNode;
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <div className="font-extrabold text-slate-800">{label}</div>
            {children}
            {error && <div className="text-xs font-bold text-rose-600">{error}</div>}
        </div>
    );
}

function BilingualField({
                            label,
                            disabled,
                            valueAr,
                            valueEn,
                            errorAr,
                            errorEn,
                            onChangeAr,
                            onChangeEn,
                            textarea,
                        }: {
    label: string;
    disabled?: boolean;
    valueAr: string;
    valueEn: string;
    errorAr?: string;
    errorEn?: string;
    onChangeAr: (v: string) => void;
    onChangeEn: (v: string) => void;
    textarea?: boolean;
}) {
    return (
        <div className="grid gap-3">
            <div className="font-extrabold text-slate-800">{label}</div>

            <div className="grid md:grid-cols-2 gap-3">
                <div>
                    <div className="text-xs mb-1">العربية</div>
                    {textarea ? (
                        <TextAreaInput disabled={disabled} value={valueAr} onChange={onChangeAr} />
                    ) : (
                        <TextInput disabled={disabled} value={valueAr} onChange={onChangeAr} />
                    )}
                    {errorAr && <div className="text-xs font-bold text-rose-600 mt-1">{errorAr}</div>}
                </div>

                <div>
                    <div className="text-xs mb-1">English</div>
                    {textarea ? (
                        <TextAreaInput disabled={disabled} value={valueEn} onChange={onChangeEn} />
                    ) : (
                        <TextInput disabled={disabled} value={valueEn} onChange={onChangeEn} />
                    )}
                    {errorEn && <div className="text-xs font-bold text-rose-600 mt-1">{errorEn}</div>}
                </div>
            </div>
        </div>
    );
}

/* =========================================================
 * Main Wizard
 * ========================================================= */
export default function Wizard({ kyc, user }: { kyc: KycProfile; user: UserPayload }) {
    const [lang, setLang] = useState<Lang>("ar");
    const dir: "rtl" | "ltr" = lang === "ar" ? "rtl" : "ltr";

    const initialStep = Math.min(Math.max(kyc.current_step || 1, 1), 6);
    const [activeStep, setActiveStep] = useState(initialStep);

    const { data, setData, post, processing, errors, clearErrors } = useForm<any>({
        ...kyc,
        is_pep: kyc.is_pep ?? false,
        acting_on_behalf: kyc.acting_on_behalf ?? false,
        declaration_accepted: kyc.declaration_accepted ?? false,
    });

    const currentStatus = kyc.status as KycProfile["status"];

    // Lock edit when submitted/active/face flow started
    const locked =
        currentStatus === "submitted" ||
        currentStatus === "verified" ||
        currentStatus === "face_required" ||
        currentStatus === "face_enrolled" ||
        currentStatus === "active";

    const needsFaceEnroll = currentStatus === "face_required";
    const needsFaceVerify = currentStatus === "face_enrolled";
    const accountActive = currentStatus === "active";

    useEffect(() => {
        const serverStep = Math.min(Math.max(kyc.current_step || 1, 1), 6);
        if (serverStep !== activeStep && (locked || serverStep > activeStep)) {
            setActiveStep(serverStep);
        }
    }, [kyc.current_step, kyc.status]);

    const steps = useMemo(
        () => [
            { n: 1, title: t(lang, "s1") },
            { n: 2, title: t(lang, "s2") },
            { n: 3, title: t(lang, "s3") },
            { n: 4, title: t(lang, "s4") },
            { n: 5, title: t(lang, "s5") },
            { n: 6, title: t(lang, "s6") },
        ],
        [lang]
    );

    const statusLabels: Record<KycProfile["status"], string> = {
        draft: t(lang, "draft"),
        submitted: t(lang, "submitted"),
        verified: t(lang, "verified"),
        face_required: lang === "ar" ? "بانتظار تسجيل الوجه" : "Face Enrollment Required",
        face_enrolled: lang === "ar" ? "بانتظار التحقق من الوجه" : "Face Verification Required",
        active: t(lang, "verified"),
        rejected: t(lang, "rejected"),
    };

    function gotoStep(n: number) {
        if (locked) return;
        const bounded = Math.min(Math.max(n, 1), 6);
        setActiveStep(bounded);
        setData("current_step", bounded);
    }

    function onSaveStep() {
        clearErrors();
        post(route("kyc.step.save", { step: activeStep }), { preserveScroll: true });
    }

    function onSubmit() {
        clearErrors();
        post(route("kyc.submit"), {
            preserveScroll: true,
            data: { declaration_accepted: !!data.declaration_accepted },
        });
    }

    const rightHeader = (
        <div className="flex items-center gap-3">
            <StatusBadge status={currentStatus} label={statusLabels[currentStatus]} />
            <button
                type="button"
                onClick={() => setLang(lang === "ar" ? "en" : "ar")}
                className="px-3 py-2 rounded-xl border border-slate-200 text-sm font-extrabold hover:bg-slate-50"
            >
                {lang === "ar" ? "English" : "العربية"}
            </button>
        </div>
    );

    const stepTitle = steps.find((x) => x.n === activeStep)?.title ?? "";

    return (
        <PageShell dir={dir}>
            <div className="grid lg:grid-cols-[320px_1fr] gap-5 items-start">
                {/* Sidebar */}
                <div className="lg:sticky lg:top-6 grid gap-4">
                    <Card title={t(lang, "kycTitle")} right={rightHeader}>
                        <div className="grid gap-2">
                            {steps.map((s) => {
                                const active = s.n === activeStep;
                                const done = s.n < activeStep;

                                return (
                                    <button
                                        key={s.n}
                                        disabled={locked}
                                        onClick={() => gotoStep(s.n)}
                                        className={cx(
                                            "w-full text-left px-4 py-3 rounded-xl border flex justify-between items-center",
                                            locked && "opacity-70 cursor-not-allowed",
                                            active
                                                ? "bg-indigo-600 border-indigo-600 text-white"
                                                : done
                                                    ? "bg-emerald-50 border-emerald-200 text-emerald-900"
                                                    : "bg-white border-slate-200 text-slate-800 hover:bg-slate-50"
                                        )}
                                    >
                    <span className="font-extrabold text-sm">
                      {t(lang, "step")} {s.n}: {s.title}
                    </span>
                                        <span className="w-7 h-7 rounded-full grid place-items-center text-xs font-black border">
                      {done ? "✓" : active ? "•" : "→"}
                    </span>
                                    </button>
                                );
                            })}
                        </div>
                    </Card>
                </div>

                {/* Main */}
                <div className="grid gap-5">
                    <Card title={stepTitle}>
                        {/* STEP 1 */}
                        {activeStep === 1 && (
                            <div className="grid gap-4">
                                <BilingualField
                                    label={t(lang, "fullName")}
                                    disabled={locked}
                                    valueAr={data.full_name_ar ?? ""}
                                    valueEn={data.full_name_en ?? ""}
                                    errorAr={errors.full_name_ar}
                                    errorEn={errors.full_name_en}
                                    onChangeAr={(v) => setData("full_name_ar", v)}
                                    onChangeEn={(v) => setData("full_name_en", v)}
                                />

                                <SingleField label={t(lang, "dob")} error={errors.date_of_birth}>
                                    <TextInput
                                        type="date"
                                        disabled={locked}
                                        value={data.date_of_birth ?? ""}
                                        onChange={(v) => setData("date_of_birth", v)}
                                    />
                                </SingleField>

                                <BilingualField
                                    label={t(lang, "nationality")}
                                    disabled={locked}
                                    valueAr={data.nationality_ar ?? ""}
                                    valueEn={data.nationality_en ?? ""}
                                    errorAr={errors.nationality_ar}
                                    errorEn={errors.nationality_en}
                                    onChangeAr={(v) => setData("nationality_ar", v)}
                                    onChangeEn={(v) => setData("nationality_en", v)}
                                />

                                <BilingualField
                                    label={t(lang, "occupation")}
                                    disabled={locked}
                                    valueAr={data.occupation_ar ?? ""}
                                    valueEn={data.occupation_en ?? ""}
                                    errorAr={errors.occupation_ar}
                                    errorEn={errors.occupation_en}
                                    onChangeAr={(v) => setData("occupation_ar", v)}
                                    onChangeEn={(v) => setData("occupation_en", v)}
                                />

                                <BilingualField
                                    label={t(lang, "employer")}
                                    disabled={locked}
                                    valueAr={data.employer_or_business_ar ?? ""}
                                    valueEn={data.employer_or_business_en ?? ""}
                                    errorAr={errors.employer_or_business_ar}
                                    errorEn={errors.employer_or_business_en}
                                    onChangeAr={(v) => setData("employer_or_business_ar", v)}
                                    onChangeEn={(v) => setData("employer_or_business_en", v)}
                                />

                                <SingleField label={t(lang, "phone")} error={errors.contact_number}>
                                    <TextInput
                                        disabled={locked}
                                        value={data.contact_number ?? ""}
                                        onChange={(v) => setData("contact_number", v)}
                                    />
                                </SingleField>

                                <SingleField label={t(lang, "email")} error={errors.email_address}>
                                    <TextInput
                                        type="email"
                                        disabled={locked}
                                        value={data.email_address ?? ""}
                                        onChange={(v) => setData("email_address", v)}
                                    />
                                </SingleField>

                                <BilingualField
                                    label={t(lang, "address")}
                                    textarea
                                    disabled={locked}
                                    valueAr={data.residential_address_ar ?? ""}
                                    valueEn={data.residential_address_en ?? ""}
                                    errorAr={errors.residential_address_ar}
                                    errorEn={errors.residential_address_en}
                                    onChangeAr={(v) => setData("residential_address_ar", v)}
                                    onChangeEn={(v) => setData("residential_address_en", v)}
                                />
                            </div>
                        )}

                        {/* STEP 2 */}
                        {activeStep === 2 && (
                            <div className="grid gap-4">
                                <SingleField label={t(lang, "idNumber")} error={errors.id_number}>
                                    <TextInput
                                        disabled={locked}
                                        value={data.id_number ?? ""}
                                        onChange={(v) => setData("id_number", v)}
                                    />
                                </SingleField>

                                <BilingualField
                                    label={t(lang, "issuingAuthority")}
                                    disabled={locked}
                                    valueAr={data.issuing_authority_ar ?? ""}
                                    valueEn={data.issuing_authority_en ?? ""}
                                    errorAr={errors.issuing_authority_ar}
                                    errorEn={errors.issuing_authority_en}
                                    onChangeAr={(v) => setData("issuing_authority_ar", v)}
                                    onChangeEn={(v) => setData("issuing_authority_en", v)}
                                />

                                <SingleField label={t(lang, "idIssue")} error={errors.id_issue_date}>
                                    <TextInput
                                        type="date"
                                        disabled={locked}
                                        value={data.id_issue_date ?? ""}
                                        onChange={(v) => setData("id_issue_date", v)}
                                    />
                                </SingleField>

                                <SingleField label={t(lang, "idExpiry")} error={errors.id_expiry_date}>
                                    <TextInput
                                        type="date"
                                        disabled={locked}
                                        value={data.id_expiry_date ?? ""}
                                        onChange={(v) => setData("id_expiry_date", v)}
                                    />
                                </SingleField>
                            </div>
                        )}

                        {/* STEP 3 */}
                        {activeStep === 3 && (
                            <div className="grid gap-4">
                                <BilingualField
                                    label={t(lang, "incomeSource")}
                                    disabled={locked}
                                    valueAr={data.source_of_income_ar ?? ""}
                                    valueEn={data.source_of_income_en ?? ""}
                                    errorAr={errors.source_of_income_ar}
                                    errorEn={errors.source_of_income_en}
                                    onChangeAr={(v) => setData("source_of_income_ar", v)}
                                    onChangeEn={(v) => setData("source_of_income_en", v)}
                                />

                                <SingleField label={t(lang, "incomeRange")} error={errors.income_range}>
                                    <TextInput
                                        disabled={locked}
                                        value={data.income_range ?? ""}
                                        onChange={(v) => setData("income_range", v)}
                                    />
                                </SingleField>

                                <BilingualField
                                    label={t(lang, "purpose")}
                                    disabled={locked}
                                    valueAr={data.purpose_of_relationship_ar ?? ""}
                                    valueEn={data.purpose_of_relationship_en ?? ""}
                                    errorAr={errors.purpose_of_relationship_ar}
                                    errorEn={errors.purpose_of_relationship_en}
                                    onChangeAr={(v) => setData("purpose_of_relationship_ar", v)}
                                    onChangeEn={(v) => setData("purpose_of_relationship_en", v)}
                                />
                            </div>
                        )}

                        {/* STEP 4 */}
                        {activeStep === 4 && (
                            <div className="grid gap-4">
                                <BoolRadio
                                    name="is_pep"
                                    disabled={locked}
                                    value={!!data.is_pep}
                                    onChange={(v) => setData("is_pep", v)}
                                    yesLabel={t(lang, "yes")}
                                    noLabel={t(lang, "no")}
                                />

                                {data.is_pep && (
                                    <BilingualField
                                        label={t(lang, "pepDetails")}
                                        textarea
                                        disabled={locked}
                                        valueAr={data.pep_details_ar ?? ""}
                                        valueEn={data.pep_details_en ?? ""}
                                        errorAr={errors.pep_details_ar}
                                        errorEn={errors.pep_details_en}
                                        onChangeAr={(v) => setData("pep_details_ar", v)}
                                        onChangeEn={(v) => setData("pep_details_en", v)}
                                    />
                                )}
                            </div>
                        )}

                        {/* STEP 5 */}
                        {activeStep === 5 && (
                            <div className="grid gap-4">
                                <BoolRadio
                                    name="acting_on_behalf"
                                    disabled={locked}
                                    value={!!data.acting_on_behalf}
                                    onChange={(v) => setData("acting_on_behalf", v)}
                                    yesLabel={t(lang, "yes")}
                                    noLabel={t(lang, "no")}
                                />

                                {data.acting_on_behalf && (
                                    <BilingualField
                                        label={t(lang, "uboDetails")}
                                        textarea
                                        disabled={locked}
                                        valueAr={data.ubo_details_ar ?? ""}
                                        valueEn={data.ubo_details_en ?? ""}
                                        errorAr={errors.ubo_details_ar}
                                        errorEn={errors.ubo_details_en}
                                        onChangeAr={(v) => setData("ubo_details_ar", v)}
                                        onChangeEn={(v) => setData("ubo_details_en", v)}
                                    />
                                )}
                            </div>
                        )}

                        {/* STEP 6 */}
                        {activeStep === 6 && (
                            <div className="grid gap-4 p-5 rounded-2xl border border-emerald-200 bg-emerald-50">
                                <div className="font-black text-slate-900">
                                    {lang === "ar" ? "إقرار ومراجعة نهائية" : "Final Declaration & Review"}
                                </div>

                                <label className="flex items-center gap-3 font-extrabold text-slate-800">
                                    <input
                                        type="checkbox"
                                        checked={!!data.declaration_accepted}
                                        disabled={processing || locked}
                                        onChange={(e) => setData("declaration_accepted", e.target.checked)}
                                    />
                                    {t(lang, "accept")}
                                </label>

                                <button
                                    type="button"
                                    disabled={processing || locked || !data.declaration_accepted}
                                    onClick={onSubmit}
                                    className="w-full px-6 py-3 rounded-xl bg-emerald-600 text-white font-extrabold hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    {lang === "ar" ? "إرسال نهائي" : "Final Submit"}
                                </button>

                                {needsFaceEnroll && (
                                    <a
                                        href={route("face.enroll")}
                                        className="block text-center px-6 py-3 rounded-xl bg-indigo-600 text-white font-extrabold hover:bg-indigo-700"
                                    >
                                        {lang === "ar" ? "الانتقال إلى تسجيل الوجه" : "Go to Face Enrollment"}
                                    </a>
                                )}

                                {needsFaceVerify && (
                                    <a
                                        href={route("face.verify")}
                                        className="block text-center px-6 py-3 rounded-xl bg-amber-600 text-white font-extrabold hover:bg-amber-700"
                                    >
                                        {lang === "ar" ? "الانتقال إلى التحقق من الوجه" : "Go to Face Verification"}
                                    </a>
                                )}

                                {accountActive && (
                                    <div className="text-center font-extrabold text-emerald-700">
                                        {lang === "ar" ? "تم تفعيل الحساب بالكامل" : "Account Fully Activated"}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* ACTION BAR */}
                        <div className="mt-7 pt-5 border-t border-slate-200 flex justify-between gap-3">
                            <button
                                type="button"
                                disabled={processing || locked || activeStep === 1}
                                onClick={() => gotoStep(activeStep - 1)}
                                className="px-4 py-2 rounded-xl border font-extrabold"
                            >
                                {t(lang, "back")}
                            </button>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={processing || locked || activeStep === 6}
                                    onClick={onSaveStep}
                                    className="px-4 py-2 rounded-xl bg-slate-900 text-white font-extrabold disabled:opacity-50"
                                >
                                    {t(lang, "save")}
                                </button>

                                <button
                                    type="button"
                                    disabled={processing || locked || activeStep >= 6}
                                    onClick={() => gotoStep(activeStep + 1)}
                                    className="px-4 py-2 rounded-xl bg-indigo-600 text-white font-extrabold disabled:opacity-50"
                                >
                                    {t(lang, "next")}
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </PageShell>
    );
}
