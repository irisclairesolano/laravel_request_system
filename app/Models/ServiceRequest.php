<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'user_id',
        'requester_name',
        'requester_email',
        'item_name',
        'quantity',
        'purpose',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
