<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $po = $this->route('purchaseOrder');

        // Hanya boleh diubah saat masih Draft
        return $po instanceof PurchaseOrder && $po->status === PurchaseOrder::STATUS_DRAFT;
    }

    public function rules(): array
    {
        return [
            'tanggal_po'       => 'required|date',
            'estimasi_datang'  => 'nullable|date|after_or_equal:tanggal_po',
            'supplier_id'      => 'required|exists:suppliers,id',
            'catatan'          => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah'   => 'required|integer|min:1',
            'items.*.harga_beli' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'items.required'       => 'Minimal satu barang harus diisi.',
            'items.min'            => 'Minimal satu barang harus diisi.',
            'items.*.jumlah.min'   => 'Qty pesan harus lebih dari nol.',
        ];
    }
}
