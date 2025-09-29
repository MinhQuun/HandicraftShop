<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PhieuNhap extends Model
{
    protected $table = 'PHIEUNHAP';
    protected $primaryKey = 'MAPN';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // Bảng dùng NGAYNHAP, không dùng created_at/updated_at


    protected $fillable = [
        'MANHACUNGCAP', 'NGAYNHAP', 'NHANVIEN_ID', 'TRANGTHAI', 'TONGSL', 'GHICHU'
    ];


    protected $casts = [
        'NGAYNHAP' => 'datetime',
        'TONGSL' => 'int',
    ];


    public function chiTiet(): HasMany
    {
        return $this->hasMany(\App\Models\ChiTietPhieuNhap::class, 'MAPN', 'MAPN');
    }


    public function nhaCungCap(): BelongsTo
    {
        return $this->belongsTo(\App\Models\NhaCungCap::class, 'MANHACUNGCAP', 'MANHACUNGCAP');
    }


    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'NHANVIEN_ID', 'id');
    }
}