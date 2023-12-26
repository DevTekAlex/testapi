<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileData extends Model
{
    use HasFactory;
    protected $table = 'files_data';
    protected $fillable = ['file_name', 'description', 'secret_key', 'file_path'];
}
