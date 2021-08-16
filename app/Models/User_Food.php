<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Food extends Model
{
    use HasFactory;
    protected $table = 'user_foods';
    protected $fillable = ['user_id', 'date', 'time', 'type', 'desc'];
}
