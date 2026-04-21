<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'customer_invoice_id'   => 'required|exists:customer_invoices,id',
            'amount'                => 'required|numeric|min:0.01',
            'payment_date'          => 'nullable|date|before_or_equal:today',
            'payment_method'        => 'required|string|in:Bank Transfer,Cash,Virtual Account,Giro/Cek',
            'bank_account_id'       => 'required|exists:bank_accounts,id',
            'reference'             => 'nullable|string|max:100',
            'giro_reference'        => 'nullable|string|max:100',
            'notes'                 => 'nullable|string|max:500',
            'payment_proof_file'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            // Conditional fields for Bank Transfer / Virtual Account
            'sender_bank_name'      => 'nullable|string|max:100',
            'sender_account_number' => 'nullable|string|max:50',
            
            // Conditional fields for Giro/Cek
            'giro_number'           => 'nullable|string|max:50',
            'giro_due_date'         => 'nullable|date',
            'issuing_bank'          => 'nullable|string|max:100',
            
            // Conditional field for Cash
            'receipt_number'        => 'nullable|string|max:50',
        ];

        // Make bank fields + reference required when payment method is Bank Transfer or Virtual Account
        if (in_array($this->payment_method, ['Bank Transfer', 'Virtual Account'])) {
            $rules['sender_bank_name'] = 'required|string|max:100';
            $rules['sender_account_number'] = 'required|string|max:50';
            $rules['reference'] = 'required|string|max:100';
        }

        // Make giro fields + giro_reference required when payment method is Giro/Cek
        if ($this->payment_method === 'Giro/Cek') {
            $rules['giro_number'] = 'required|string|max:50';
            $rules['giro_due_date'] = 'required|date';
            $rules['issuing_bank'] = 'required|string|max:100';
            $rules['giro_reference'] = 'required|string|max:100';
        }

        // Make receipt number required when payment method is Cash
        if ($this->payment_method === 'Cash') {
            $rules['receipt_number'] = 'required|string|max:50';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_invoice_id.required'   => 'Invoice pelanggan wajib dipilih.',
            'amount.required'                => 'Nominal pembayaran wajib diisi.',
            'amount.min'                     => 'Nominal pembayaran minimal Rp 1.',
            'payment_method.required'        => 'Metode pembayaran wajib dipilih.',
            'payment_method.in'              => 'Metode pembayaran tidak valid.',
            'payment_date.before_or_equal'   => 'Tanggal pembayaran tidak boleh di masa depan.',
            'bank_account_id.required'       => 'Bank penerima (Medikindo) wajib dipilih.',
            'payment_proof_file.required'    => 'Bukti pembayaran wajib diupload.',
            'payment_proof_file.mimes'       => 'Format file harus JPG, PNG, atau PDF.',
            'payment_proof_file.max'         => 'Ukuran file maksimal 5MB.',
            'sender_bank_name.required'      => 'Nama bank pengirim wajib dipilih.',
            'sender_account_number.required' => 'Nomor rekening pengirim wajib diisi.',
            'reference.required'             => 'Nomor referensi transfer wajib diisi.',
            'giro_number.required'           => 'Nomor giro/cek wajib diisi.',
            'giro_due_date.required'         => 'Tanggal jatuh tempo giro/cek wajib diisi.',
            'issuing_bank.required'          => 'Bank penerbit giro/cek wajib diisi.',
            'giro_reference.required'        => 'Nomor referensi giro/cek wajib diisi.',
            'receipt_number.required'        => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
        ];
    }
}
