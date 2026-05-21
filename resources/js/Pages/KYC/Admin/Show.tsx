import React from "react";
import { useForm, Link } from "@inertiajs/react";

type KycStatus =
    | "draft"
    | "submitted"
    | "verified"
    | "face_required"
    | "face_enrolled"
    | "active"
    | "rejected";

export default function Show({ kyc }: any) {
    // تحصين بسيط لو فيه مسافات/كيس مختلف
    const status: KycStatus = String(kyc.status || "")
        .trim()
        .toLowerCase() as KycStatus;

    const verifyForm = useForm<{ office_remarks: string }>({
        office_remarks: kyc.office_remarks ?? "",
    });

    const rejectForm = useForm<{ rejection_reason: string }>({
        rejection_reason: "",
    });

    return (
        <div className="max-w-5xl mx-auto px-4 py-8">
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div className="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div className="font-extrabold text-lg">KYC #{kyc.id}</div>
                    <div className="font-bold uppercase text-sm">{status}</div>
                </div>

                <div className="p-5 grid gap-6">
                    <div className="grid md:grid-cols-2 gap-3 text-sm">
                        <div><b>User:</b> {kyc.user?.name ?? "—"}</div>
                        <div><b>Email:</b> {kyc.user?.email ?? "—"}</div>
                        <div><b>Submitted:</b> {kyc.submitted_at ?? "—"}</div>
                        <div><b>Verified at:</b> {kyc.verified_at ?? "—"}</div>
                        <div><b>Verified by (ID):</b> {kyc.verified_by ?? "—"}</div>
                    </div>

                    <div className="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                        <div className="font-extrabold mb-2">Office Remarks</div>
                        <textarea
                            value={verifyForm.data.office_remarks}
                            onChange={(e) => verifyForm.setData("office_remarks", e.target.value)}
                            className="w-full min-h-[100px] px-3 py-2 rounded-xl border border-slate-200"
                        />
                    </div>

                    {status === "submitted" && (
                        <div className="flex flex-wrap gap-3 items-center">
                            <button
                                type="button"
                                onClick={() => verifyForm.post(`/admin/kyc/${kyc.id}/verify`)}
                                disabled={verifyForm.processing}
                                className="px-4 py-2 rounded-xl bg-indigo-600 text-white font-extrabold hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Approve KYC (Require Face Enroll)
                            </button>

                            <div className="flex-1" />

                            <input
                                placeholder="Rejection reason"
                                value={rejectForm.data.rejection_reason}
                                onChange={(e) => rejectForm.setData("rejection_reason", e.target.value)}
                                className="px-3 py-2 rounded-xl border border-slate-200 flex-1"
                            />

                            <button
                                type="button"
                                onClick={() => rejectForm.post(`/admin/kyc/${kyc.id}/reject`)}
                                disabled={rejectForm.processing}
                                className="px-4 py-2 rounded-xl bg-rose-600 text-white font-extrabold hover:bg-rose-700 disabled:opacity-50"
                            >
                                Reject
                            </button>
                        </div>
                    )}

                    {status === "face_required" && (
                        <div className="p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 text-sm">
                            KYC approved. Waiting for face enrollment by user.
                        </div>
                    )}

                    {status === "face_enrolled" && (
                        <div className="p-4 rounded-2xl border border-sky-200 bg-sky-50 text-sky-900 text-sm">
                            Face enrolled successfully. Account can be activated.
                        </div>
                    )}

                    {status === "active" && (
                        <div className="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 text-sm">
                            User is fully verified and active.
                        </div>
                    )}

                    {status === "rejected" && (
                        <div className="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-900 text-sm">
                            <div className="font-extrabold mb-1">Rejected</div>
                            <div>{kyc.rejection_reason ?? "—"}</div>
                        </div>
                    )}

                    <div>
                        <Link
                            href="/admin/kyc"
                            className="inline-block mt-2 text-sm font-bold text-slate-700 hover:underline"
                        >
                            ← Back to list
                        </Link>

                    </div>
                    <div className="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div className="font-extrabold text-lg">KYC #{kyc.id}</div>

                        <div className="flex items-center gap-3">
                            <span className="font-bold uppercase text-sm">{status}</span>

                            <a
                                href={route("admin.kyc.print", kyc.id)}
                                target="_blank"
                                className="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-extrabold hover:bg-black"
                            >
                                Print PDF
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    );
}
