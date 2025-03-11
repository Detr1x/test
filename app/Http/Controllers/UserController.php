<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tables;
class UserController extends Controller
{
    public function index()
{
    $user = auth()->user();
    
    // Получаем таблицы, к которым у пользователя есть доступ
    $accessList = is_array($user->association) ? $user->association : explode(',', $user->association);
    $tables = Tables::whereIn('access', $accessList)->get();
    

    return view('welcome', compact('tables'));
}

}
