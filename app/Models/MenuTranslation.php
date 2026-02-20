<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{
    protected $table = 'm_menu_translations';
    protected $fillable = ['menu_id', 'locale', 'name'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
