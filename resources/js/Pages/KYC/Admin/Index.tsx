import React from "react";
import { Link } from "@inertiajs/react";

/* =========================
 * Types
 * ========================= */
type KycStatus =
    | "draft"
    | "submitted"
    | "verified"
    | "face_required"
    | "face_enrolled"
    | "active"
    | "rejected";

type Kyc = {
    id: number;
    status: KycStatus;
    updated_at: string;
    user?: {
        id: number;
        email: string;
        name?: string;
    };
};

type Pagination<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export default function Index({
                                  kycs,
                                  filters,
                              }: {
    kycs: Pagination<Kyc>;
    filters: { status?: string };
}) {
    const statuses: KycStatus[] = [
        "draft",
        "submitted",
        "verified",
        "face_required",
        "face_enrolled",
        "active",
        "rejected",
    ];

    const statusBadge = (status: KycStatus) => {
        const map: Record<KycStatus, string> = {
            draft: "bg-slate-100 text-slate-700",
            submitted: "bg-amber-100 text-amber-800",
            verified: "bg-indigo-100 text-indigo-800",
            face_required: "bg-sky-100 text-sky-800",
            face_enrolled: "bg-teal-100 text-teal-800",
            active: "bg-emerald-100 text-emerald-800",
            rejected: "bg-rose-100 text-rose-800",
        };

        return (
            <span className={`px-2.5 py-1 rounded-full text-xs font-extrabold ${map[status]}`}>
                {status.toUpperCase()}
            </span>
        );
    };

    return (
        <div className="max-w-6xl mx-auto px-4 py-8">
            <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                {/* Header */}
                <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div className="font-extrabold text-lg">KYC Review</div>
                    <div className="text-sm text-slate-600">
                        Total records: {kycs.total}
                    </div>
                </div>

                <div className="p-6">
                    {/* Filters */}
                    <div className="flex flex-wrap gap-2 mb-6">
                        <Link
                            href="/admin/kyc"
                            className={`px-3 py-1.5 rounded-xl border text-sm font-bold ${
                                !filters?.status
                                    ? "bg-slate-900 text-white border-slate-900"
                                    : "border-slate-200 hover:bg-slate-50"
                            }`}
                        >
                            ALL
                        </Link>

                        {statuses.map((s) => {
                            const active = filters?.status === s;
                            return (
                                <Link
                                    key={s}
                                    href={`/admin/kyc?status=${s}`}
                                    className={`px-3 py-1.5 rounded-xl border text-sm font-bold transition ${
                                        active
                                            ? "bg-slate-900 text-white border-slate-900"
                                            : "border-slate-200 hover:bg-slate-50"
                                    }`}
                                >
                                    {s.toUpperCase()}
                                </Link>
                            );
                        })}
                    </div>

                    {/* Table */}
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm border-collapse">
                            <thead>
                            <tr className="border-b bg-slate-50 text-left">
                                <th className="py-2 px-3">ID</th>
                                <th className="py-2 px-3">User</th>
                                <th className="py-2 px-3">Email</th>
                                <th className="py-2 px-3">Status</th>
                                <th className="py-2 px-3">Updated</th>
                                <th className="py-2 px-3 text-right"></th>
                            </tr>
                            </thead>
                            <tbody>
                            {kycs.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="py-8 text-center text-slate-500"
                                    >
                                        No KYC records found.
                                    </td>
                                </tr>
                            )}

                            {kycs.data.map((k) => (
                                <tr
                                    key={k.id}
                                    className="border-b hover:bg-slate-50"
                                >
                                    <td className="py-2 px-3 font-bold">
                                        {k.id}
                                    </td>
                                    <td className="py-2 px-3">
                                        {k.user?.name ?? "—"}
                                    </td>
                                    <td className="py-2 px-3">
                                        {k.user?.email ?? "—"}
                                    </td>
                                    <td className="py-2 px-3">
                                        {statusBadge(k.status)}
                                    </td>
                                    <td className="py-2 px-3">
                                        {k.updated_at}
                                    </td>
                                    <td className="py-2 px-3 text-right">
                                        <Link
                                            href={`/admin/kyc/${k.id}`}
                                            className="px-3 py-1.5 rounded-xl border border-slate-200 font-bold hover:bg-slate-50"
                                        >
                                            Open
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="flex items-center justify-between mt-6 text-sm">
                        <div>
                            Page {kycs.current_page} of {kycs.last_page}
                        </div>
                        <div className="flex gap-4 font-bold">
                            {kycs.prev_page_url && (
                                <a
                                    href={kycs.prev_page_url}
                                    className="hover:underline"
                                >
                                    ← Prev
                                </a>
                            )}
                            {kycs.next_page_url && (
                                <a
                                    href={kycs.next_page_url}
                                    className="hover:underline"
                                >
                                    Next →
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
