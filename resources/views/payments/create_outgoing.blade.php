<x-layout title="Kas Keluar (Outgoing)" pageTitle="Pengeluaran Dana ke Supplier" breadcrumb="Akuntansi > Kas Keluar">

    <div class="flex justify-center">
        <div class="w-full xl:w-2/3 lg:w-4/5">
            <x-card title="Formulir Pengeluaran Kas (AP)">
                <form method="POST" action="{{ route('web.payments.store.outgoing') }}" class="flex flex-col gap-6">
                    @csrf

                    <div class="grid md:grid-cols-2 grid-cols-1 gap-5">
                        {{-- Select AP Invoice --}}
                        <div class="col-span-1 md:col-span-2">
                            <x-select name="supplier_invoice_id" label="Hutang / Faktur AP Terkait" required onchange="updateMaxAmount(this)">
                                <option value="">— Pilih Tagihan AP yang Belum Terlunasi —</option>
                                @foreach($invoices as $inv)
                                    @php
                                        $outstanding = $inv->total_amount - $inv->paid_amount;
                                    @endphp
                                    <option value="{{ $inv->id }}" data-outstanding="{{ $outstanding }}">
                                        {{ $inv->invoice_number }} - {{ $inv->supplier?->name }} (Tagihan: Rp {{ number_format($outstanding, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="col-span-1">
                            <x-input type="date" name="payment_date" label="Tanggal Dikirim" required value="{{ old('payment_date', date('Y-m-d')) }}" />
                        </div>
                        <div class="col-span-1">
                            <x-select name="payment_method" label="Metode Kas Keluar" required>
                                <option value="Corporate Transfer">Corporate Transfer</option>
                                <option value="Cek / Giro">Cek / Giro</option>
                                <option value="Cash">Cash Kecil</option>
                            </x-select>
                        </div>

                        <div class="col-span-1">
                            <x-input type="number" name="amount" id="out-amount-input" label="Nominal Dilepas (Rp)" required min="1" step="1"
                                     placeholder="Mis. 500000" hint="Pilih tagihan AP dahulu" hintId="out-outstanding-helper" />
                        </div>
                        <div class="col-span-1">
                            <x-input type="text" name="reference" label="Nomor Bukti Transfer (Opsional)" placeholder="BUKTI-..." value="{{ old('reference') }}" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-border mt-3">
                        <x-button variant="secondary" outline href="{{ route('web.payments.index') }}">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="danger">
                            Otorisasi Pengeluaran
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateMaxAmount(select) {
            let option = select.options[select.selectedIndex];
            let max = option.getAttribute('data-outstanding');
            let input = document.getElementById('out-amount-input');
            let helper = document.getElementById('out-outstanding-helper');

            if(max) {
                input.setAttribute('max', max);
                input.value = max;
                if (helper) {
                    helper.innerText = "Max hutang: Rp " + parseInt(max).toLocaleString('id-ID');
                    helper.classList.remove("text-muted-foreground");
                    helper.classList.add("text-danger");
                }
            } else {
                input.removeAttribute('max');
                input.value = "";
                if (helper) {
                    helper.innerText = "Pilih tagihan AP dahulu";
                    helper.classList.remove("text-danger");
                    helper.classList.add("text-muted-foreground");
                }
            }
        }
    </script>
    @endpush
</x-layout>
