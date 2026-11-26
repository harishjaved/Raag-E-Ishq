        <!-- Sticky Modal Header -->
        <div class="sticky top-0 z-10 bg-[#0e1424]/95 backdrop-blur-md flex items-start justify-between gap-3 border-b border-slate-800 -mx-5 -mt-5 sm:-mx-6 sm:-mt-6 px-5 sm:px-6 pt-4 sm:pt-5 pb-4 rounded-t-3xl">
            <div class="space-y-1 min-w-0">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>WhatsApp QR Ticket Dispatch</span>
                </div>
                <h3 id="modalCustomerTitle" class="text-lg font-bold text-slate-100 font-serif-display truncate">{{ $modalTitle }}</h3>
                <p id="modalSubtitle" class="text-xs text-slate-400">{{ $modalSubtitle }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('home') }}"
                    aria-label="Close and go to main page"
                    title="Go to Main Page"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full bg-slate-800 hover:bg-amber-500 text-slate-200 hover:text-slate-950 border border-slate-700 hover:border-amber-400 text-[11px] font-bold transition">
                    <span>Main Page</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                <button type="button" onclick="closeWhatsAppModal()"
                    aria-label="Close WhatsApp modal"
                    title="Close modal"
                    class="w-9 h-9 rounded-full bg-slate-800 hover:bg-rose-500 text-slate-100 hover:text-white border border-slate-600 hover:border-rose-400 flex items-center justify-center transition shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
