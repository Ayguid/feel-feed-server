<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APIPasswordResetToken extends Model
{
    use HasFactory;
    protected $table = 'api_password_reset_tokens';
    protected $fillable = ['user_id', 'token_signature', 'token_type', 'used_token', 'expires_at'];
}
