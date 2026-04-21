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
            'customer_invoice_id' => 'required|exists:customer_invoices,id',
            'payment_type'        => 'required|in:full,partial',
            'amount'              => 'required|numeric|min:0.01',
            'payment_date'        => 'required|date|before_or_equal:today',
            'bank_reference'      => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:500',
            'file'                => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ];
    }

    /**
     * Add after-validation business rule checks.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $invoiceId = $this->input('customer_invoice_id');
            $amount    = (float) $this->input('amount');
            $type      = $this->input('payment_type');

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
                // Force amount to match outstanding (backend override — ignore JS value)
                // We don't fail here; we'll override in service. Just sanity-check.
                if ($amount > $outstanding + 0.01) {
                    $validator->errors()->add('amount', 'Jumlah bayar penuh tidak boleh melebihi sisa tagihan (Rp ' . number_format($outstanding, 0, ',', '.') . ').');
                }
            } elseif ($type === 'partial') {
                if ($amount >= $outstanding) {
                    $validator->errors()->add('amount', 'Untuk bayar sebagian, nominal harus kurang dari total tagihan tersisa (Rp ' . number_format($outstanding, 0, ',', '.') . '). Gunakan "Bayar Penuh" untuk melunasi.');
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
            'file.required'                => 'Bukti transfer wajib diunggah.',
            'file.mimes'                   => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'file.max'                     => 'Ukuran file maksimal 5MB.',
        ];
    }
}
