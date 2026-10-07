<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $table = 'requests';
    protected $fillable = [
        'user_id', 
        'service_id', 
        'status', 
        'description', 
        'created_at', 
        'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
