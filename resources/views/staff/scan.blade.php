<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gate Scanner — Raag-E-Ishq</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; touch-action: manipulation; }
        #reader video { border-radius: 1rem; width: 100% !important; height: auto !important; }
        #reader__scan_region { background: rgba(0,0,0,0.4); border-radius: 1rem; }
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #020617 inset;
            -webkit-text-fill-color: #f1f5f9;
            caret-color: #f1f5f9;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100">
    <!-- Top Navigation Bar -->
    <header class="border-b border-[#D4AF37]/30 bg-[#2C060E]/95 backdrop-blur sticky top-0 z-50 px-4 py-3">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#D4AF37]/20 text-[#E5C158] font-bold text-xs flex items-center justify-center border border-[#D4AF37]/40">GS</span>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-[#E5C158]">Gate Operations</div>
                    <div class="text-sm font-bold text-[#FAF6F0]">{{ $user->name }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="sound_toggle_btn" onclick="toggleGateSound()" class="text-[11px] px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-semibold transition">Siren: ON</button>
                <span id="gate_indicator" class="text-xs px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300 font-semibold">GATE 1</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs px-2.5 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-rose-400 font-semibold transition">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-md w-full mx-auto p-4 space-y-4">
        <!-- Live Tri-State Result Display Overlay -->
        <div id="result_container" class="hidden transition-all duration-300 transform">
            <!-- Dynamic Result Card injected via JS -->
        </div>

        <!-- Camera Scanner Section -->
        <div class="p-4 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-3">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                <span>Live Camera View</span>
                <button id="toggle_camera_btn" onclick="toggleCamera()" class="text-amber-400 hover:text-amber-300">Pause Camera</button>
            </div>

            <div id="reader" class="overflow-hidden rounded-2xl border border-slate-800 bg-black min-h-[260px] flex items-center justify-center text-slate-600 text-xs">
                <span>Initializing camera stream...</span>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                <span>Point at guest's Admission QR ticket</span>
                <span>Today Admitted: <strong id="admitted_count" class="text-emerald-400 font-bold">{{ $todayCheckinsCount }}</strong></span>
            </div>
        </div>

        <!-- Manual Lookup Accordion -->
        <div class="p-4 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
            <div class="flex items-center justify-between cursor-pointer" onclick="toggleManualLookup()">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Manual Lookup (Damaged QR)</span>
                </div>
                <span id="manual_toggle_icon" class="text-xs text-slate-400">&darr;</span>
            </div>

            <form id="manual_section" class="hidden space-y-3 pt-2 border-t border-slate-800" onsubmit="event.preventDefault(); submitManualLookup();">
                <div class="space-y-1">
                    <label for="manual_ticket_number" class="text-[11px] font-semibold text-slate-400">Ticket Number</label>
                    <input type="text" id="manual_ticket_number" placeholder="e.g. TKT-ABCD-1234"
                        oninput="this.value = this.value.toUpperCase()"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 uppercase text-xs focus:outline-none focus:border-amber-400 font-mono">
                </div>

                <div class="space-y-1">
                    <label for="manual_reason" class="text-[11px] font-semibold text-slate-400">Audit Reason / Justification <span class="text-rose-400">*</span></label>
                    <input type="text" id="manual_reason" placeholder="e.g. Screen cracked, verified Govt Photo ID"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 text-xs focus:outline-none focus:border-amber-400">
                </div>

                <div id="manual_inline_feedback" class="hidden rounded-xl px-3 py-2 text-xs font-semibold border"></div>

                <button type="submit" id="manual_submit_btn"
                    class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold text-xs transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Admit via Manual Override
                </button>
            </form>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrCode = null;
        let isProcessing = false;
        let cameraRunning = false;
        let admittedCounter = {{ $todayCheckinsCount }};
        let resultAutoHideTimer = null;

        // Web Audio Synthesizer for Gate Feedback & Denial Siren
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        const audioCtx = AudioContextClass ? new AudioContextClass() : null;
        let soundEnabled = true;

        function unlockAudioContext() {
            if (!audioCtx || !soundEnabled) return Promise.resolve();
            if (audioCtx.state === 'suspended') {
                return audioCtx.resume().catch(() => {});
            }
            return Promise.resolve();
        }

        ['pointerdown', 'keydown', 'click'].forEach(evt => {
            document.addEventListener(evt, () => {
                unlockAudioContext();
            }, { passive: true });
        });

        function toggleGateSound() {
            soundEnabled = !soundEnabled;
            const btn = document.getElementById('sound_toggle_btn');
            if (btn) {
                btn.textContent = soundEnabled ? 'Siren: ON' : 'Siren: OFF';
                btn.className = soundEnabled
                    ? 'text-[11px] px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-semibold transition'
                    : 'text-[11px] px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-400 font-semibold transition';
            }
            if (soundEnabled) unlockAudioContext();
        }

        function withAudioReady(fn) {
            if (!audioCtx || !soundEnabled) return;
            unlockAudioContext().then(() => {
                try {
                    fn(audioCtx.currentTime);
                } catch (e) {
                    console.warn('Audio feedback failed:', e);
                }
            });
        }

        function playSuccessSound() {
            withAudioReady((now) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(660, now);
                osc.frequency.setValueAtTime(990, now + 0.11);
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.32);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.33);
            });
        }

        function playSiren(mode = 'wail') {
            try {
                if (navigator.vibrate) navigator.vibrate([180, 80, 180, 80, 260]);
            } catch (e) {}

            withAudioReady((now) => {
                const duration = mode === 'hilo' ? 1.2 : 1.45;
                const master = audioCtx.createGain();
                master.gain.setValueAtTime(0.001, now);
                master.gain.exponentialRampToValueAtTime(0.45, now + 0.04);
                master.gain.setValueAtTime(0.45, now + duration - 0.12);
                master.gain.exponentialRampToValueAtTime(0.001, now + duration);
                master.connect(audioCtx.destination);

                // Dual slightly-detuned oscillators produce an unmistakable security siren
                const osc1 = audioCtx.createOscillator();
                const osc2 = audioCtx.createOscillator();
                osc1.type = 'sawtooth';
                osc2.type = 'square';

                if (mode === 'hilo') {
                    const tones = [880, 620, 880, 620, 880, 620];
                    const step = duration / tones.length;
                    tones.forEach((hz, idx) => {
                        osc1.frequency.setValueAtTime(hz, now + idx * step);
                        osc2.frequency.setValueAtTime(hz * 1.01, now + idx * step);
                    });
                } else {
                    osc1.frequency.setValueAtTime(520, now);
                    osc1.frequency.exponentialRampToValueAtTime(1020, now + 0.36);
                    osc1.frequency.exponentialRampToValueAtTime(500, now + 0.72);
                    osc1.frequency.exponentialRampToValueAtTime(1020, now + 1.08);
                    osc1.frequency.exponentialRampToValueAtTime(460, now + duration);

                    osc2.frequency.setValueAtTime(525, now);
                    osc2.frequency.exponentialRampToValueAtTime(1030, now + 0.36);
                    osc2.frequency.exponentialRampToValueAtTime(505, now + 0.72);
                    osc2.frequency.exponentialRampToValueAtTime(1030, now + 1.08);
                    osc2.frequency.exponentialRampToValueAtTime(465, now + duration);
                }

                const filter = audioCtx.createBiquadFilter();
                filter.type = 'lowpass';
                filter.frequency.setValueAtTime(2200, now);

                osc1.connect(filter);
                osc2.connect(filter);
                filter.connect(master);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + duration + 0.02);
                osc2.stop(now + duration + 0.02);
            });
        }

        function playDuplicateSound() { playSiren('hilo'); }
        function playInvalidSound() { playSiren('wail'); }

        // Tri-State UI Card Renderer
        function showResultCard(type, title, message, details = {}) {
            const container = document.getElementById('result_container');
            container.classList.remove('hidden');

            let colorClasses = '';
            let iconSvg = '';

            if (type === 'GREEN_VALID') {
                playSuccessSound();
                colorClasses = 'bg-emerald-950 border-emerald-500 text-emerald-100 shadow-emerald-500/20';
                iconSvg = `<svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
            } else if (type === 'ORANGE_ALREADY_USED') {
                playDuplicateSound();
                colorClasses = 'bg-amber-950 border-amber-500 text-amber-100 shadow-amber-500/20';
                iconSvg = `<svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            } else {
                playInvalidSound();
                colorClasses = 'bg-rose-950 border-rose-500 text-rose-100 shadow-rose-500/20';
                iconSvg = `<svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }

            container.innerHTML = `
                <div class="p-5 rounded-3xl border-2 ${colorClasses} shadow-2xl space-y-3 animate-bounce-short">
                    <div class="flex items-center gap-3">
                        ${iconSvg}
                        <div>
                            <div class="text-xs uppercase font-extrabold tracking-wider">${title}</div>
                            <div class="text-sm font-semibold">${message}</div>
                        </div>
                    </div>
                    ${details.attendee_name ? `
                        <div class="pt-2 border-t border-slate-700/60 text-xs space-y-1">
                            <div class="flex justify-between"><span>Attendee:</span> <strong class="text-white">${details.attendee_name}</strong></div>
                            <div class="flex justify-between"><span>Category:</span> <span class="font-bold text-amber-400">${details.category || 'General'}</span></div>
                            <div class="flex justify-between"><span>Ticket #:</span> <span class="font-mono">${details.ticket_number || ''}</span></div>
                            ${details.checked_in_at ? `<div class="flex justify-between text-[11px] text-slate-400"><span>Admitted At:</span> <span>${details.checked_in_at}</span></div>` : ''}
                        </div>
                    ` : ''}
                    <button onclick="dismissResult()" class="w-full mt-2 py-2 rounded-xl bg-black/40 hover:bg-black/60 text-xs font-bold transition">
                        Scan Next Ticket
                    </button>
                </div>
            `;

            // Clear any prior timer so back-to-back checks don't get hidden early
            if (resultAutoHideTimer) {
                clearTimeout(resultAutoHideTimer);
            }
            resultAutoHideTimer = setTimeout(dismissResult, 10000);
            container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function dismissResult() {
            if (resultAutoHideTimer) {
                clearTimeout(resultAutoHideTimer);
                resultAutoHideTimer = null;
            }
            document.getElementById('result_container').classList.add('hidden');
            isProcessing = false;
        }

        // Camera Initialization
        function startCamera() {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 220, height: 220 }, aspectRatio: 1.0 };
            
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                cameraRunning = true;
                document.getElementById('toggle_camera_btn').textContent = 'Pause Camera';
            }).catch(err => {
                console.error("Camera error:", err);
                document.getElementById('reader').innerHTML = `
                    <div class="p-4 text-center text-rose-400 space-y-1">
                        <div>Camera Access Denied or Unavailable</div>
                        <div class="text-[10px] text-slate-500">Please allow browser camera permissions or use Manual Lookup below.</div>
                    </div>
                `;
            });
        }

        function toggleCamera() {
            if (!html5QrCode) return;
            if (cameraRunning) {
                html5QrCode.stop().then(() => {
                    cameraRunning = false;
                    document.getElementById('toggle_camera_btn').textContent = 'Resume Camera';
                });
            } else {
                startCamera();
            }
        }

        // Handle Decoded QR Payload
        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;

            try {
                const url = new URL(decodedText);
                const uuid = url.searchParams.get('u');
                const sig = url.searchParams.get('s');

                if (!uuid || !sig) {
                    showResultCard('RED_INVALID', 'Invalid QR Code', 'This is not a valid Raag-E-Ishq admission ticket.');
                    return;
                }

                verifyScan(uuid, sig);
            } catch (e) {
                showResultCard('RED_INVALID', 'Unreadable Format', 'QR code format is not recognized.');
            }
        }

        function onScanFailure(error) {
            // Noisy scan frames ignored
        }

        // Verify with Backend (use same-origin relative URL to avoid host/scheme mismatches)
        function verifyScan(uuid, signature) {
            fetch("{{ route('staff.scan.verify', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ uuid: uuid, signature: signature, gate: 'GATE_1' })
            })
            .then(res => res.json().then(data => ({ status: res.status, data: data })))
            .then(res => {
                if (res.status === 200) {
                    admittedCounter++;
                    document.getElementById('admitted_count').textContent = admittedCounter;
                    showResultCard('GREEN_VALID', 'Admitted — Valid Entry', res.data.message, res.data);
                } else if (res.status === 409) {
                    showResultCard('ORANGE_ALREADY_USED', 'Already Checked In', res.data.message, res.data);
                } else {
                    showResultCard('RED_INVALID', 'Entry Rejected', res.data.message, res.data);
                }
            })
            .catch(err => {
                console.error('Verify error:', err);
                showResultCard('RED_INVALID', 'Network Error', 'Could not reach verification server. Retry scan.');
            });
        }

        // Manual Lookup Drawer
        function toggleManualLookup() {
            const sec = document.getElementById('manual_section');
            const icon = document.getElementById('manual_toggle_icon');
            if (sec.classList.contains('hidden')) {
                sec.classList.remove('hidden');
                icon.innerHTML = '&uarr;';
            } else {
                sec.classList.add('hidden');
                icon.innerHTML = '&darr;';
            }
        }

        function setManualFeedback(type, message) {
            const box = document.getElementById('manual_inline_feedback');
            if (!box) return;
            box.classList.remove('hidden', 'bg-emerald-950/70', 'border-emerald-500/40', 'text-emerald-300', 'bg-amber-950/70', 'border-amber-500/40', 'text-amber-300', 'bg-rose-950/70', 'border-rose-500/40', 'text-rose-300');
            if (type === 'GREEN_VALID') {
                box.classList.add('bg-emerald-950/70', 'border-emerald-500/40', 'text-emerald-300');
            } else if (type === 'ORANGE_ALREADY_USED') {
                box.classList.add('bg-amber-950/70', 'border-amber-500/40', 'text-amber-300');
            } else {
                box.classList.add('bg-rose-950/70', 'border-rose-500/40', 'text-rose-300');
            }
            box.textContent = message;
        }

        function submitManualLookup() {
            const ticketInput = document.getElementById('manual_ticket_number');
            const reasonInput = document.getElementById('manual_reason');
            const submitBtn = document.getElementById('manual_submit_btn');
            const ticketNum = ticketInput.value.trim().toUpperCase();
            const reason = reasonInput.value.trim();

            ticketInput.value = ticketNum;

            if (!ticketNum || reason.length < 3) {
                setManualFeedback('RED_INVALID', 'Enter a valid Ticket Number and an Audit Reason (at least 3 characters).');
                showResultCard('RED_INVALID', 'Missing Details', 'Enter both Ticket Number and Audit Reason (minimum 3 characters).');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying Ticket...';

            fetch("{{ route('staff.lookup.manual', [], false) }}", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ticket_number: ticketNum, reason: reason, gate: 'GATE_1' })
            })
            .then(async res => {
                const rawText = await res.text();
                let data = {};
                try {
                    data = rawText ? JSON.parse(rawText) : {};
                } catch (e) {
                    data = { message: res.status === 419 ? 'Session expired — please refresh the page and log in again.' : 'Unexpected server response (HTTP ' + res.status + ').' };
                }
                return { status: res.status, data: data };
            })
            .then(res => {
                const validationMsg = res.data.errors ? Object.values(res.data.errors).flat()[0] : null;
                const msg = validationMsg || res.data.message || 'Verification failed.';

                if (res.status === 200) {
                    admittedCounter++;
                    document.getElementById('admitted_count').textContent = admittedCounter;
                    setManualFeedback('GREEN_VALID', msg + (res.data.attendee_name ? ' — ' + res.data.attendee_name : ''));
                    showResultCard('GREEN_VALID', 'Manual Entry Admitted', msg, res.data);
                } else if (res.status === 409) {
                    setManualFeedback('ORANGE_ALREADY_USED', msg);
                    showResultCard('ORANGE_ALREADY_USED', 'Already Checked In', msg, res.data);
                } else {
                    setManualFeedback('RED_INVALID', msg);
                    showResultCard('RED_INVALID', 'Manual Entry Rejected', msg, res.data);
                }
            })
            .catch(() => {
                const netMsg = 'Manual lookup request failed. Check network connection or reload the page.';
                setManualFeedback('RED_INVALID', netMsg);
                showResultCard('RED_INVALID', 'Network Error', netMsg);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Admit via Manual Override';
            });
        }

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
