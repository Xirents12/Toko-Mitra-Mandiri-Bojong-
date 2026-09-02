<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerimaPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses sudah dijamin oleh middleware role (gudang)
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                 => 'required|array|min:1',
            'items.*.detail_id'     => 'required|exists:po_details,id',
            'items.*.qty_diterima'  => 'required|integer|min:0',
            'items.*.harga_beli'    => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'             => 'Tidak ada barang untuk diproses.',
            'items.*.qty_diterima.min'   => 'Qty diterima tidak boleh negatif.',
        ];
    }
}
