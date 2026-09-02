<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Hanya Gudang yang boleh membuat PERMINTAAN barang (Admin yang menyetujui permintaan).
     */
    public function create(User $user): bool
    {
        return $user->isGudang();
    }

    /**
     * DIMATIKAN: "Buat PO ke Supplier" untuk admin — admin cukup Setujui/Tolak permintaan.
     * (Route & tombol sudah dikomentari; policy ini hanya dipertahankan untuk referensi.)
     */
    // public function buatPo(User $user, PurchaseOrder $po): bool
    // {
    //     return $user->isAdmin()
    //         && $po->status === PurchaseOrder::STATUS_PERMINTAAN;
    // }

    /**
     * Hanya boleh mengubah PO yang masih Draft (oleh Gudang).
     */
    public function update(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang()
            && $po->status === PurchaseOrder::STATUS_DRAFT;
    }

    /**
     * Mengajukan PO untuk persetujuan (hanya dari status Draft).
     * Pemilik (Admin) tidak mengajukan ke dirinya sendiri — ia menyetujui langsung.
     */
    public function ajukan(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang()
            && $po->status === PurchaseOrder::STATUS_DRAFT;
    }

    /**
     * Pemilik menyetujui PO/permintaan: Draft, Permintaan Barang (dari Gudang),
     * maupun yang diajukan (Menunggu Persetujuan).
     * "Buat PO ke Supplier" untuk admin dimatikan — admin cukup Setujui / Tolak.
     */
    public function setujui(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin()
            && in_array($po->status, [
                PurchaseOrder::STATUS_PERMINTAAN,
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_MENUNGGU,
            ]);
    }

    public function tolak(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin() && in_array($po->status, [
            PurchaseOrder::STATUS_PERMINTAAN,
            PurchaseOrder::STATUS_MENUNGGU,
        ]);
    }

    /**
     * Menandai PO dikirim supplier (hanya Gudang).
     */
    public function kirim(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang()
            && $po->status === PurchaseOrder::STATUS_DISETUJUI;
    }

    /**
     * Penerimaan barang hanya oleh Bagian Gudang.
     */
    public function terima(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang() && $po->bisa_diterima;
    }

    /**
     * Membatalkan PO yang masih Draft / Permintaan / Menunggu Persetujuan (hanya Gudang).
     */
    public function batalkan(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang()
            && in_array($po->status, [
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_PERMINTAAN,
                PurchaseOrder::STATUS_MENUNGGU,
            ]);
    }

    /**
     * Menghapus hanya PO Draft (soft delete, oleh Gudang).
     */
    public function delete(User $user, PurchaseOrder $po): bool
    {
        return $user->isGudang()
            && $po->status === PurchaseOrder::STATUS_DRAFT;
    }
}
