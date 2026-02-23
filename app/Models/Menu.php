<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'm_menus';
    protected $fillable = [
        'code',
        'parent_id',
        'icon',
        'route',
        'order',
        'is_active',
        'translations'
    ];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('order');
    }

    public function translations()
    {
        return $this->hasMany(MenuTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale ??= app()->getLocale();

        return $this->hasOne(MenuTranslation::class)
            ->where('locale', $locale);
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'm_role_menus',  // pivot table
            'menu_id',
            'role_id'
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'm_user_menus',
            'menu_id',
            'user_id'
        );
    }
}
