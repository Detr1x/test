<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Columns extends Model
{
    use HasFactory;

    protected $fillable = ['table_token', 'column_token', 'name', 'type', 's_number'];

    // Связь с таблицей (много колонок -> одна таблица)
    public function table()
    {
        return $this->belongsTo(Tables::class, 'table_token', 'table_token');
    }

    // Связь с данными (один столбец -> много данных)
    public function columnData()
    {
        return $this->hasMany(Column_data::class, 'column_token', 'column_token');
    }
}
