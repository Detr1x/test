<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column_data extends Model
{
    use HasFactory;
    protected $table = 'column_data';
    protected $fillable = ['table_token', 'column_token', 'data', 'type', 's_number'];

    // Связь с таблицей
    public function table()
    {
        return $this->belongsTo(Tables::class, 'table_token', 'table_token');
    }

    // Связь с колонкой
    public function column()
    {
        return $this->belongsTo(Columns::class, 'column_token', 'column_token');
    }
}
