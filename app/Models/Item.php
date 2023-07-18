<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'text'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('user', function ($query) {
            if($user = auth()->user()) 
                $query->where('user_id', $user->id);
        });
    }

    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->orderBy('id', 'DESC');
    }
}
