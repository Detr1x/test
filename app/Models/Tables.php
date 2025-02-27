<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{
    use HasFactory;

    protected $fillable = ['table_token', 'name'];

    // Связь с колонками (одна таблица -> много колонок)
    public function columns()
    {
        return $this->hasMany(Columns::class, 'table_token', 'table_token');
    }

    // Связь с данными (одна таблица -> много данных)
    public function columnData()
    {
        return $this->hasMany(Column_data::class, 'table_token', 'table_token');
    }
}
