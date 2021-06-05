<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /*
    |------------------------------------------------------------------------------------
    | Relations
    |------------------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /*
    |------------------------------------------------------------------------------------
    | scopes
    |------------------------------------------------------------------------------------
    */
    public function scopeMine($q)
    {
        $q->where('user_id', auth()->id());
    }

    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
    public function getIsMineAttribute()
    {
        return $this->user_id === auth()->id();
    }
}
