<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses sudah dijamin oleh middleware role (admin & kasir)
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_po'       => 'required|date',
            'estimasi_datang'  => 'nullable|date|after_or_equal:tanggal_po',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'catatan'          => 'nullable|string|max:1000',
            'action'           => 'nullable|in:draft,ajukan,setujui',
            'items'            => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.jumlah'   => 'required|integer|min:1',
            'items.*.harga_beli' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.exists'      => 'Supplier tidak valid.',
            'items.*.supplier_id.exists' => 'Supplier pada item tidak valid.',
            'items.required'          => 'Minimal satu barang harus diisi.',
            'items.min'               => 'Minimal satu barang harus diisi.',
            'items.*.jumlah.min'      => 'Qty pesan harus lebih dari nol.',
            'estimasi_datang.after_or_equal' => 'Estimasi datang tidak boleh sebelum tanggal PO.',
        ];
    }
}
