import React, { useEffect, useRef, useState } from "react";

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
};

type ResultState = "success" | "failed" | null;

/* =========================================================
 * Error Messages
 * ========================================================= */
const ERROR_MESSAGES: Record<string, string> = {
    already_enrolled:
        "This face is already registered. You do not need to enroll again.",
    liveness_failed:
        "Liveness verification failed. Please follow the instructions carefully.",
    face_not_clear:
        "Your face was not clear. Ensure good lighting and keep your face centered.",
    multiple_faces:
        "Multiple faces detected. Please make sure only one person is visible.",
    challenge_invalid:
        "Verification session expired. Please try again.",
    enroll_failed:
        "Face enrollment failed. Please try again.",
    network_error:
        "Unable to contact the verification server. Please try again later.",
    unknown:
        "Verification failed due to an unexpected error. Please try again.",
};

/* =========================================================
 * Component
 * ========================================================= */
export default function Enroll() {
    /* =========================
     * Refs
     * ========================= */
    const videoRef = useRef<HTMLVideoElement | null>(null);
    const recorderRef = useRef<MediaRecorder | null>(null);
    const chunksRef = useRef<BlobPart[]>([]);

    /* =========================
     * State
     * ========================= */
    const [stream, setStream] = useState<MediaStream | null>(null);
    const [challenge, setChallenge] = useState<Challenge | null>(null);

    const [recording, setRecording] = useState(false);
    const [videoBlob, setVideoBlob] = useState<Blob | null>(null);

    const [stepText, setStepText] = useState("Preparing…");
    const [countdown, setCountdown] = useState<number | null>(null);

    const [result, setResult] = useState<ResultState>(null);
    const [error, setError] = useState<string | null>(null);
    const [errorReason, setErrorReason] = useState<string | null>(null);

    /* =========================
     * Utils
     * ========================= */
    const sleep = (ms: number) =>
        new Promise<void>((resolve) => setTimeout(resolve, ms));

    const runCountdown = async (seconds: number, text: string) => {
        setStepText(text);
        for (let i = seconds; i > 0; i--) {
            setCountdown(i);
            await sleep(1000);
        }
        setCountdown(null);
    };

    const resetFlow = () => {
        setResult(null);
        setError(null);
        setErrorReason(null);
        setVideoBlob(null);
        setStepText("Preparing…");
        setCountdown(null);
    };

    /* =========================
     * Open Camera
     * ========================= */
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
            .catch(() => {
                setError("Camera access denied.");
            });

        return () => {
            stream?.getTracks().forEach((t) => t.stop());
        };
    }, []);

    /* =========================
     * Load Challenge (AUTH SAFE)
     * ========================= */
    const loadChallenge = async () => {
        try {
            const res = await fetch("/api/face/challenge", {
                credentials: "same-origin",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const data = await res.json();

            if (!Array.isArray(data?.actions)) {
                throw new Error();
            }

            setChallenge(data);
        } catch {
            setError("Failed to load verification instructions.");
        }
    };

    useEffect(() => {
        loadChallenge();
    }, []);

    /* =========================
     * Start Recording
     * ========================= */
    const startRecording = async () => {
        if (!stream || !challenge) return;

        resetFlow();
        chunksRef.current = [];

        const mimeType = MediaRecorder.isTypeSupported("video/webm;codecs=vp8")
            ? "video/webm;codecs=vp8"
            : "video/webm";

        const recorder = new MediaRecorder(stream, { mimeType });
        recorderRef.current = recorder;

        recorder.ondataavailable = (e) => {
            if (e.data.size > 0) {
                chunksRef.current.push(e.data);
            }
        };

        recorder.onstop = () => {
            const blob = new Blob(chunksRef.current, { type: mimeType });
            if (blob.size < 50_000) {
                setError("Recording failed.");
                return;
            }
            setVideoBlob(blob);
            setStepText("Recording complete");
        };

        recorder.start();
        setRecording(true);

        await runCountdown(2, "Look straight at the camera");

        for (const action of challenge.actions) {
            switch (action) {
                case "turn_left":
                    await runCountdown(3, "Turn LEFT");
                    break;
                case "turn_right":
                    await runCountdown(3, "Turn RIGHT");
                    break;
                case "smile":
                    await runCountdown(2, "Smile");
                    break;
                case "blink":
                    await runCountdown(2, "Blink");
                    break;
                default:
                    await runCountdown(2, action);
            }
        }

        await runCountdown(1, "Hold still");
        recorder.stop();
        setRecording(false);
    };

    /* =========================
     * Submit Enrollment (NO 422)
     * ========================= */
    const submitEnrollment = async () => {
        if (!videoBlob || !challenge) return;

        await fetch("/sanctum/csrf-cookie", {
            credentials: "same-origin",
        });

        const csrf = getCookie("XSRF-TOKEN");
        if (!csrf) {
            setError(ERROR_MESSAGES.unknown);
            return;
        }

        const form = new FormData();
        form.append("video", videoBlob, "enroll.webm");
        form.append("challenge_id", challenge.challenge_id);

        try {
            const res = await fetch("/api/face/enroll", {
                method: "POST",
                body: form,
                credentials: "same-origin",
                headers: {
                    "X-XSRF-TOKEN": csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const data = await res.json();

            if (data?.success === true) {
                setResult("success");
                return;
            }

            const reason = data?.reason ?? "unknown";
            setResult("failed");
            setErrorReason(reason);
            setError(ERROR_MESSAGES[reason] ?? ERROR_MESSAGES.unknown);
        } catch {
            setResult("failed");
            setError(ERROR_MESSAGES.network_error);
        }
    };

    const isAlreadyEnrolled = errorReason === "already_enrolled";

    /* =========================
     * UI
     * ========================= */
    return (
        <div className="min-h-screen bg-neutral-100 flex items-center justify-center px-4">
            <div className="w-full max-w-4xl bg-white rounded-2xl shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b">
                    <h1 className="text-lg font-semibold">Face Verification</h1>
                    <p className="text-sm text-slate-500">
                        Follow the instructions to complete verification
                    </p>
                </div>

                <div className="grid lg:grid-cols-2">
                    <div className="relative bg-black aspect-[3/4]">
                        <video
                            ref={videoRef}
                            muted
                            playsInline
                            className="w-full h-full object-cover"
                        />
                        {countdown !== null && (
                            <div className="absolute top-4 right-4 w-10 h-10 rounded-full bg-white flex items-center justify-center font-semibold">
                                {countdown}
                            </div>
                        )}
                        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 px-4 py-2 rounded-full bg-black/70 text-white text-sm">
                            {stepText}
                        </div>
                    </div>

                    <div className="p-6 space-y-4">
                        {!recording && !videoBlob && challenge && (
                            <button
                                onClick={startRecording}
                                className="w-full py-3 rounded-xl bg-slate-900 text-white"
                            >
                                Start verification
                            </button>
                        )}

                        {videoBlob && !result && (
                            <>
                                <video
                                    src={URL.createObjectURL(videoBlob)}
                                    controls
                                    className="w-full rounded-xl"
                                />
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        onClick={resetFlow}
                                        className="border rounded-lg py-2"
                                    >
                                        Re-record
                                    </button>
                                    <button
                                        onClick={submitEnrollment}
                                        className="bg-slate-900 text-white rounded-lg py-2"
                                    >
                                        Submit
                                    </button>
                                </div>
                            </>
                        )}

                        {result === "success" && (
                            <div className="border border-emerald-200 bg-emerald-50 p-4 rounded-xl">
                                Verification successful
                            </div>
                        )}

                        {error && (
                            <div className="text-sm text-red-600">
                                {error}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
