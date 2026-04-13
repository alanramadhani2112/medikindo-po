<x-layout title="Kas Masuk (Incoming)" pageTitle="Penerimaan Dana Klinik" breadcrumb="Akuntansi > Kas Masuk">

    <div class="flex justify-center">
        <div class="w-full xl:w-2/3 lg:w-4/5">
            <x-card title="Formulir Pemasukan Kas (AR)">
                <form method="POST" action="{{ route('web.payments.store.incoming') }}" class="flex flex-col gap-6">
                    @csrf

                    <div class="grid md:grid-cols-2 grid-cols-1 gap-5">
                        {{-- Select AR Invoice --}}
                        <div class="col-span-1 md:col-span-2">
                            <x-select name="customer_invoice_id" label="Piutang / Faktur AR Terkait" required onchange="updateMaxAmount(this)">
                                <option value="">— Pilih Tagihan AR yang Belum Lunas —</option>
                                @foreach($invoices as $inv)
                                    @php
                                        $outstanding = $inv->total_amount - $inv->paid_amount;
                                    @endphp
                                    <option value="{{ $inv->id }}" data-outstanding="{{ $outstanding }}">
                                        {{ $inv->invoice_number }} - {{ $inv->organization?->name }} (Sisa: Rp {{ number_format($outstanding, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="col-span-1">
                            <x-input type="date" name="payment_date" label="Tanggal Terterima" required value="{{ old('payment_date', date('Y-m-d')) }}" />
                        </div>
                        <div class="col-span-1">
                            <x-select name="payment_method" label="Metode Bayar" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Virtual Account">Virtual Account</option>
                            </x-select>
                        </div>

                        <div class="col-span-1">
                            <x-input type="number" name="amount" id="amount-input" label="Nominal Disetor (Rp)" required min="1" step="1"
                                     placeholder="Mis. 500000" hint="Pilih faktur terlebih dahulu" hintId="outstanding-helper" />
                        </div>
                        <div class="col-span-1">
                            <x-input type="text" name="reference" label="Nomor Referensi (Opsional)" placeholder="TRF-..." value="{{ old('reference') }}" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-border mt-3">
                        <x-button variant="secondary" outline href="{{ route('web.payments.index') }}">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="success">
                            Rekam Kas Masuk
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
            let input = document.getElementById('amount-input');
            let helper = document.getElementById('outstanding-helper');

            if(max) {
                input.setAttribute('max', max);
                input.value = max;
                if (helper) {
                    helper.innerText = "Max pelunasan: Rp " + parseInt(max).toLocaleString('id-ID');
                    helper.classList.remove("text-danger");
                    helper.classList.add("text-success");
                }
            } else {
                input.removeAttribute('max');
                input.value = "";
                if (helper) {
                    helper.innerText = "Pilih faktur terlebih dahulu";
                    helper.classList.remove("text-success");
                    helper.classList.remove("text-danger");
                    helper.classList.add("text-muted-foreground");
                }
            }
        }
    </script>
    @endpush
</x-layout>
