<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verifikasi extends Model
{
    use HasFactory;

    protected $table = 't_verifikasis';
    protected $primaryKey = 'id';

    protected $fillable = [
        'mahasiswa_id',
        'penghargaan_id',
        'dosen_id',
        'admin_id',
        'verifikasi_admin_status',
        'verifikasi_admin_keterangan',
        'verifikasi_admin_tanggal',
        'verifikasi_dosen_status',
        'verifikasi_dosen_keterangan',
        'verifikasi_dosen_tanggal',
        'verifikasi_visible',
        'verifikasi_verified_at'
    ];

    protected $casts = [
        'verifikasi_admin_tanggal' => 'datetime',
        'verifikasi_dosen_tanggal' => 'datetime',
        'verifikasi_visible' => 'boolean'
    ];

    /**
     * Check if verification is fully approved (both admin and dosen approved)
     */
    public function isFullyVerified(): bool
    {
        return $this->verifikasi_admin_status === 'Diterima' && 
               $this->verifikasi_dosen_status === 'Diterima';
    }

    /**
     * Check if verification is rejected by either admin or dosen
     */
    public function isRejected(): bool
    {
        return $this->verifikasi_admin_status === 'Ditolak' || 
               $this->verifikasi_dosen_status === 'Ditolak';
    }

    /**
     * Check if verification is pending (either or both are waiting)
     */
    public function isPending(): bool
    {
        return ($this->verifikasi_admin_status === 'Menunggu' || 
                $this->verifikasi_dosen_status === 'Menunggu') && 
               !$this->isRejected();
    }

    /**
     * Scope for fully verified records
     */
    public function scopeFullyVerified($query)
    {
        return $query->where('verifikasi_admin_status', 'Diterima')
                    ->where('verifikasi_dosen_status', 'Diterima');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'id');
    }

    public function penghargaan(): BelongsTo
    {
        return $this->belongsTo(Penghargaan::class, 'penghargaan_id', 'id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    /**
     * Update verification completion timestamp if both verifications are accepted
     */
    public function updateVerificationCompletion(): void
    {
        if ($this->isFullyVerified() && !$this->verifikasi_verified_at) {
            $this->update(['verifikasi_verified_at' => now()]);
        }
    }
}
