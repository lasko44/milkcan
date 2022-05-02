<?php

namespace Milkcan\Whitelabel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhitelabelDomain extends Model
{
    protected $fillable=[
        'domain',
        'folder'
    ];
}
