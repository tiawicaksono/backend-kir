<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrnSinkron extends Model
{
    protected $table = 'trn_sinkrons';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $fillable = [
        'api_integration_id',
        'name',
        'url_api',
        'token',
        'prefix',
        'status',
        'keterangan',
        'created_at',
    ];

    public function apiIntegration()
    {
        return $this->belongsTo(ApiIntegration::class);
    }
}
