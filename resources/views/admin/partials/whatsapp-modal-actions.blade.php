            <!-- Secondary Actions Row -->
            <div class="grid grid-cols-2 gap-2.5">
                <!-- 2. Download QR Image (PNG) -->
                <button type="button" id="modalDownloadPngBtn"
                    class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download QR (PNG)</span>
                </button>

                <!-- 3. Copy Text Message -->
                <button type="button" id="modalCopyTextBtn"
                    class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Copy Message Text</span>
                </button>
            </div>

            <!-- Cross Close & Main Page Navigation Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-slate-800/80">
                <button type="button" onclick="closeWhatsAppModal()"
                    class="py-2.5 px-4 rounded-xl bg-slate-800/90 hover:bg-rose-500/20 text-slate-200 hover:text-rose-200 border border-slate-700 hover:border-rose-500/40 font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Close &amp; Back to Office</span>
                </button>
                <a href="{{ route('home') }}"
                    class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-amber-500/20 text-amber-300 hover:text-amber-200 border border-amber-500/30 font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Go to Main Page</span>
                </a>
            </div>
