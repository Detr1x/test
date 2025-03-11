<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{
    use HasFactory;

    protected $fillable = ['table_token', 'name', 'access'];

    // Связь с колонками (одна таблица -> много колонок)
    public function columns()
    {
        return $this->hasMany(Columns::class, 'table_token', 'table_token')->select('table_token', 'id');
    }
    
    public function rows()
{
    return $this->hasOne(Column_data::class, 'table_token', 'table_token')
        ->selectRaw('table_token, MAX(s_number) as max_rows')
        ->groupBy('table_token');
}

    // Связь с данными (одна таблица -> много данных)
    public function columnData()
    {
        return $this->hasMany(Column_data::class, 'table_token', 'table_token');
    }
}
