import React, { useEffect, useMemo, useRef, useState } from "react";

/* =========================================================
 * Helpers
 * ========================================================= */
function getCookie(name: string): string | null {
    const match = document.cookie.match(
        new RegExp("(^| )" + name + "=([^;]+)")
    );
    return match ? decodeURIComponent(match[2]) : null;
}

/* =========================================================
 * Types
 * ========================================================= */
type Challenge = {
    challenge_id: string;
    actions: string[];
    time_limit: number;
    expires_at: string;
};

type HumanResult = {
    type: "success" | "error";
    title: string;
    message: string;
};

/* =========================================================
 * Labels
 * ========================================================= */
function actionLabel(a: string) {
    switch (a) {
        case "blink":
            return "ارمش بعينيك";
        case "smile":
            return "ابتسم";
        case "turn_left":
            return "لف رأسك يسارًا";
        case "turn_right":
            return "لف رأسك يمينًا";
        default:
            return a;
    }
}

/* =========================================================
 * Component
 * ========================================================= */
export default function Verify() {
    const videoRef = useRef<HTMLVideoElement | null>(null);
    const recorderRef = useRef<MediaRecorder | null>(null);
    const chunksRef = useRef<BlobPart[]>([]);

    const [stream, setStream] = useState<MediaStream | null>(null);
    const [recording, setRecording] = useState(false);
    const [videoBlob, setVideoBlob] = useState<Blob | null>(null);

    const [challenge, setChallenge] = useState<Challenge | null>(null);
    const [sessionId, setSessionId] = useState<string | null>(null);

    const [stepText, setStepText] = useState("جاري التحضير…");
    const [countdown, setCountdown] = useState<number | null>(null);

    const [result, setResult] = useState<any>(null);
    const [error, setError] = useState<string | null>(null);

    const mimeType = useMemo(
        () =>
            MediaRecorder.isTypeSupported("video/webm;codecs=vp8")
                ? "video/webm;codecs=vp8"
                : "video/webm",
        []
    );

    const sleep = (ms: number) => new Promise<void>((r) => setTimeout(r, ms));

    /* =========================================================
     * Init
     * ========================================================= */
    useEffect(() => {
        navigator.mediaDevices
            .getUserMedia({ video: { facingMode: "user" }, audio: false })
            .then((s) => {
                setStream(s);
                if (videoRef.current) {
                    videoRef.current.srcObject = s;
                    videoRef.current.play();
                }
            })
            .catch(() => setError("لم يتم السماح بالوصول إلى الكاميرا"));

        initVerification();
    }, []);

    async function initVerification() {
        setResult(null);
        setVideoBlob(null);
        setError(null);
        await startSession();
        await fetchChallenge();
    }

    async function startSession() {
        try {
            const res = await fetch("/api/face/start", {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();
            setSessionId(data.session_id ?? null);
        } catch {
            setSessionId(null);
        }
    }

    async function fetchChallenge() {
        try {
            const res = await fetch("/api/face/challenge", {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();
            setChallenge(data);
            setStepText("التحدي جاهز");
        } catch {
            setError("تعذر تحميل التحدي");
        }
    }

    const runCountdown = async (sec: number, text: string) => {
        setStepText(text);
        for (let i = sec; i > 0; i--) {
            setCountdown(i);
            await sleep(1000);
        }
        setCountdown(null);
    };

    /* =========================================================
     * Record
     * ========================================================= */
    const record = async () => {
        if (!stream || !challenge) return;

        if (new Date(challenge.expires_at).getTime() < Date.now()) {
            setError("انتهت صلاحية التحدي، يرجى البدء من جديد");
            return;
        }

        setError(null);
        setVideoBlob(null);
        chunksRef.current = [];

        const rec = new MediaRecorder(stream, { mimeType });
        recorderRef.current = rec;

        rec.ondataavailable = (e) => e.data.size && chunksRef.current.push(e.data);
        rec.onstop = () => {
            const blob = new Blob(chunksRef.current, { type: mimeType });
            if (blob.size < 120_000) {
                setError("الفيديو قصير جدًا، حاول مرة أخرى");
                return;
            }
            setVideoBlob(blob);
            setStepText("تم التسجيل بنجاح");
        };

        rec.start();
        setRecording(true);

        await runCountdown(2, "انظر مباشرة إلى الكاميرا");
        for (const a of challenge.actions) {
            await runCountdown(3, actionLabel(a));
        }
        await runCountdown(1, "اثبت مكانك");

        rec.stop();
        setRecording(false);
    };

    /* =========================================================
     * Verify
     * ========================================================= */
    const verify = async () => {
        if (!videoBlob || !challenge) return;

        setError(null);
        setResult(null);

        await fetch("/sanctum/csrf-cookie", { credentials: "same-origin" });

        const csrfToken = getCookie("XSRF-TOKEN");
        if (!csrfToken) {
            setError("فشل التحقق الأمني (CSRF)");
            return;
        }

        const form = new FormData();
        form.append("video", videoBlob, "verify.webm");
        form.append("challenge_id", challenge.challenge_id);
        if (sessionId) form.append("session_id", sessionId);

        const res = await fetch("/api/face/verify", {
            method: "POST",
            body: form,
            credentials: "same-origin",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-XSRF-TOKEN": csrfToken,
            },
        });

        const data = await res.json();
        setResult(data);
    };

    /* =========================================================
     * Human Message Mapper
     * ========================================================= */
    function humanMessage(result: any): HumanResult | null {
        if (!result) return null;

        if (result.authorized === true) {
            return {
                type: "success",
                title: "تم التحقق بنجاح",
                message:
                    "تم التحقق من هويتك بنجاح، وسيتم تفعيل حسابك الآن.",
            };
        }

        switch (result.reason) {
            case "challenge_invalid":
                return {
                    type: "error",
                    title: "انتهت صلاحية التحقق",
                    message: "يرجى إعادة المحاولة وبدء تحقق جديد.",
                };

            case "liveness_failed":
                return {
                    type: "error",
                    title: "فشل التحقق الحيوي",
                    message:
                        "تعذر التأكد من حيوية الوجه. تأكد من الإضاءة واتبع التعليمات.",
                };

            case "not_match":
                return {
                    type: "error",
                    title: "الوجه غير مطابق",
                    message:
                        "الوجه لا يطابق البيانات المسجلة. يرجى المحاولة مرة أخرى.",
                };

            default:
                return {
                    type: "error",
                    title: "فشل التحقق",
                    message:
                        "حدث خطأ غير متوقع أثناء التحقق. يرجى المحاولة مرة أخرى.",
                };
        }
    }

    const human = useMemo(() => humanMessage(result), [result]);

    /* =========================================================
     * UI
     * ========================================================= */
    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-50 p-6">
            <div className="w-full max-w-xl bg-white rounded-2xl shadow p-6 space-y-6">

                <h1 className="text-xl font-bold text-slate-800">
                    التحقق من الوجه
                </h1>

                <div className="relative rounded-xl overflow-hidden bg-black">
                    <video
                        ref={videoRef}
                        muted
                        playsInline
                        className="w-full aspect-video object-cover"
                    />
                    {countdown !== null && (
                        <div className="absolute top-3 right-3 w-10 h-10 rounded-full bg-white flex items-center justify-center font-bold">
                            {countdown}
                        </div>
                    )}
                    <div className="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm">
                        {stepText}
                    </div>
                </div>

                <div className="flex gap-3">
                    <button
                        onClick={record}
                        disabled={recording || !challenge}
                        className="flex-1 py-3 rounded-xl bg-indigo-600 text-white font-semibold disabled:opacity-50"
                    >
                        {recording ? "جاري التسجيل…" : "تسجيل"}
                    </button>

                    <button
                        onClick={verify}
                        disabled={!videoBlob || recording}
                        className="flex-1 py-3 rounded-xl bg-slate-900 text-white font-semibold disabled:opacity-50"
                    >
                        تحقق
                    </button>
                </div>

                <button
                    onClick={initVerification}
                    disabled={recording}
                    className="w-full py-2 rounded-lg border font-medium"
                >
                    بدء تحقق جديد
                </button>

                {error && (
                    <div className="rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">
                        {error}
                    </div>
                )}

                {human && (
                    <div
                        className={`rounded-xl p-4 border ${
                            human.type === "success"
                                ? "bg-emerald-50 border-emerald-200 text-emerald-800"
                                : "bg-red-50 border-red-200 text-red-800"
                        }`}
                    >
                        <div className="font-bold mb-1">{human.title}</div>
                        <div className="text-sm">{human.message}</div>
                    </div>
                )}
            </div>
        </div>
    );
}
