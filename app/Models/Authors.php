<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
    use HasFactory;
    protected $table = "authors";
    public $timestamps = false;
    /**
     * @var array<int,string>
     */

    protected $fillable = [
        "author_description",
        "join_at",
        "balance",
        "user_id"
    ];
}
