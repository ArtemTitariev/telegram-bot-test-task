<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class TrelloUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trello_id',
        'username',
        // 'full_name',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'trello_user_id');
    }
}
