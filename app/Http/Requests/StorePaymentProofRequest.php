<?php

namespace App\Http\Requests;

use App\Models\CustomerInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/policy
    }

    public function rules(): array
    {
        return [
            'customer_invoice_id'    => 'required|exists:customer_invoices,id',
            'payment_type'           => 'required|in:full,partial',
            'amount'                 => 'required|numeric|min:0.01',
            'payment_date'           => 'required|date|before_or_equal:today',
            'payment_method'         => 'required|string|in:Bank Transfer,Virtual Account,Giro/Cek,Cash',
            'sender_bank_name'       => 'nullable|string|max:200',
            'sender_account_number'  => 'nullable|string|max:50',
            'bank_reference'         => 'nullable|string|max:100',
            'giro_number'            => 'nullable|string|max:100',
            'giro_due_date'          => 'nullable|date|after:today',
            'notes'                  => 'nullable|string|max:500',
            'file'                   => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ];
    }

    /**
     * Add after-validation business rule checks.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $invoiceId     = $this->input('customer_invoice_id');
            $amount        = (float) $this->input('amount');
            $type          = $this->input('payment_type');
            $paymentMethod = $this->input('payment_method');

            // DEBUG: Log all inputs
            \Log::info('Payment Proof Validation Debug', [
                'payment_method' => $paymentMethod,
                'sender_bank_name' => $this->input('sender_bank_name'),
                'sender_account_number' => $this->input('sender_account_number'),
                'all_inputs' => $this->all(),
            ]);

            if (!$invoiceId || $amount <= 0 || !$type) {
                return;
            }

            $invoice = CustomerInvoice::find($invoiceId);
            if (!$invoice) {
                return;
            }

            $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;

            if ($outstanding <= 0) {
                $validator->errors()->add('customer_invoice_id', 'Invoice ini sudah lunas dan tidak dapat menerima pembayaran.');
                return;
            }

            if ($type === 'full') {
                if ($amount > $outstanding + 0.01) {
                    $validator->errors()->add('amount', 'Jumlah bayar penuh tidak boleh melebihi sisa tagihan (Rp ' . number_format($outstanding, 0, ',', '.') . ').');
                }
            } elseif ($type === 'partial') {
                if ($amount >= $outstanding) {
                    $validator->errors()->add('amount', 'Untuk bayar sebagian, nominal harus kurang dari total tagihan tersisa (Rp ' . number_format($outstanding, 0, ',', '.') . '). Gunakan "Bayar Penuh" untuk melunasi.');
                }
            }

            // Validate bank name required for bank methods
            $bankMethods = ['Bank Transfer', 'Virtual Account', 'Giro/Cek'];
            if (in_array($paymentMethod, $bankMethods)) {
                $bankName = trim($this->input('sender_bank_name', ''));
                if (empty($bankName)) {
                    $validator->errors()->add('sender_bank_name', 'Nama bank wajib dipilih untuk metode pembayaran ' . $paymentMethod . '.');
                }
            }

            // Validate account number required for Bank Transfer and Virtual Account
            $accountRequiredMethods = ['Bank Transfer', 'Virtual Account'];
            if (in_array($paymentMethod, $accountRequiredMethods)) {
                $accountNumber = trim($this->input('sender_account_number', ''));
                if (empty($accountNumber)) {
                    $validator->errors()->add('sender_account_number', 'Nomor rekening pengirim wajib diisi untuk metode pembayaran ' . $paymentMethod . '.');
                }
            }

            // Validate giro fields if payment method is Giro/Cek
            if ($paymentMethod === 'Giro/Cek') {
                $giroNumber = trim($this->input('giro_number', ''));
                if (empty($giroNumber)) {
                    $validator->errors()->add('giro_number', 'Nomor giro/cek wajib diisi untuk metode pembayaran Giro/Cek.');
                }
                if (!$this->input('giro_due_date')) {
                    $validator->errors()->add('giro_due_date', 'Tanggal jatuh tempo giro/cek wajib diisi.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_invoice_id.required' => 'Invoice pelanggan wajib dipilih.',
            'customer_invoice_id.exists'   => 'Invoice yang dipilih tidak valid.',
            'payment_type.required'        => 'Jenis pembayaran wajib dipilih.',
            'payment_type.in'              => 'Jenis pembayaran hanya boleh: Bayar Penuh atau Bayar Sebagian.',
            'amount.required'              => 'Nominal pembayaran wajib diisi.',
            'amount.numeric'               => 'Nominal pembayaran harus berupa angka.',
            'amount.min'                   => 'Nominal pembayaran minimal Rp 1.',
            'payment_date.required'        => 'Tanggal pembayaran wajib diisi.',
            'payment_date.before_or_equal' => 'Tanggal pembayaran tidak boleh di masa depan.',
            'payment_method.required'      => 'Metode pembayaran wajib dipilih.',
            'payment_method.in'            => 'Metode pembayaran tidak valid.',
            'file.required'                => 'Bukti transfer wajib diunggah.',
            'file.mimes'                   => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'file.max'                     => 'Ukuran file maksimal 5MB.',
        ];
    }
}
