<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quyen extends Model
{
    protected $table = 'QUYEN';
    protected $primaryKey = 'MAQUYEN';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['MAQUYEN', 'TENQUYEN'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'QUYEN_NGUOIDUNG', 'MAQUYEN', 'user_id');
    }
}
