<x-layout title="Toolbar Demo" pageTitle="Filter Toolbar Standardization" :breadcrumb="$breadcrumbs ?? []">
    <div class="p-0">
        {{-- Standardized Filter Toolbar Implementation --}}
        <x-toolbar>
            {{-- SEARCH SLOT --}}
            <x-slot:search>
                <div class="relative group w-full md:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Search PO, supplier, or items..." 
                        class="w-full bg-white border border-slate-200 rounded-lg pl-10 pr-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/20 focus:border-slate-900 transition-shadow">
                </div>
            </x-slot:search>

            {{-- FILTERS SLOT --}}
            <x-slot:filters>
                <select class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/20 transition-shadow">
                    <option value="">Filter by Status</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                </select>
                
                <select class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/20 transition-shadow">
                    <option value="">Last 30 Days</option>
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                </select>
            </x-slot:filters>

            {{-- ACTIONS SLOT --}}
            <x-slot:actions>
                <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-colors bg-white border border-slate-200 rounded-lg text-slate-700 hover:bg-slate-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export PDF
                </button>
                <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-slate-900 hover:bg-slate-800 focus:ring-2 focus:ring-slate-900/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New PO
                </button>
            </x-slot:actions>
        </x-toolbar>

        {{-- Example Data Table --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Recent Transactions</h3>
            </div>
            <div class="h-64 flex flex-col items-center justify-center gap-2">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-500 font-medium">Table content would be rendered here</p>
                <p class="text-xs text-slate-400">Standardizing UI across the enterprise system</p>
            </div>
        </div>
    </div>
</x-layout>
