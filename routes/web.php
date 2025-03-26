<?php

use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckAdmin;
use App\Exports\TableExport;
use Maatwebsite\Excel\Facades\Excel;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', 'App\Http\Controllers\UserController@index')
    ->name('home');
    Route::get('/table/{token}', 'App\Http\Controllers\UserController@showUserTable')
    ->name('user_table');
    Route::post('/save-cell-data', 'App\Http\Controllers\UserController@saveCellData')
    ->name('save_table_data');
    Route::get('/logout', 'App\Http\Controllers\AuthController@logout')
        ->name('logout');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', 'App\Http\Controllers\AuthController@showLoginForm')
        ->name('login_show');
    Route::post('/login', 'App\Http\Controllers\AuthController@login')
        ->name('login');
});

Route::middleware(['admin'])->group(function () {
    Route::get('/admin', 'App\Http\Controllers\AdminController@showAdmin')
        ->name('admin');
    Route::get('/admin/users', 'App\Http\Controllers\AdminController@showUsers')
        ->name('users');
    Route::get('/admin/tables', 'App\Http\Controllers\AdminController@showTables')
        ->name('tables');
    Route::get('/admin/table/{token}', 'App\Http\Controllers\AdminController@showTable')
        ->name('table');

    Route::get('/create_user', 'App\Http\Controllers\AdminController@showUserCreateForm')
        ->name('create_user');
    Route::post('/create_user', 'App\Http\Controllers\AdminController@create_user')
        ->name('create_user');

    Route::get('/create_table', 'App\Http\Controllers\AdminController@showTableCreateForm')
        ->name('create_table');
    Route::post('/create_table', 'App\Http\Controllers\AdminController@create_table')
        ->name('create_table');

    Route::get('/admin/create_table/{token}/columns', 'App\Http\Controllers\AdminController@showTableColumnsCreateForm')
        ->name('admin.create_table.columns');
    Route::post('/admin/create_table/{token}/columns/store', 'App\Http\Controllers\AdminController@columns_store')
        ->name('admin.create_table.columns_store');

    Route::get('/admin/create_table/{token}/titles', 'App\Http\Controllers\AdminController@showTableTitlesCreateForm')
        ->name('admin.create_table.titles');
    Route::post('/admin/create_table/{token}/titles/store', 'App\Http\Controllers\AdminController@titles_store')
        ->name('admin.create_table.titles_store');

    Route::get('/admin/create_table/{token}/filling', 'App\Http\Controllers\AdminController@showTableDataFillingForm')
        ->name('admin.create_table.data');
    Route::post('/admin/create_table/{token}/filling/store', 'App\Http\Controllers\AdminController@filling_store')
        ->name('admin.create_table.data_store');

    Route::get('/tables/{table_token}/edit-data', 'App\Http\Controllers\AdminController@showEditTableData')
        ->name('edit_table_data');
    Route::post('/tables/{table_token}/update-data', 'App\Http\Controllers\AdminController@updateTableData')
        ->name('update_table_data');
        
    Route::get('/search-users', 'App\Http\Controllers\AdminController@searchUsers');
    Route::get('/search-tables', 'App\Http\Controllers\AdminController@searchTables');



    Route::get('/export/{tableToken}', function ($tableToken) {
    return Excel::download(new TableExport($tableToken), $tableToken . '.xlsx');
    })->name('export.table');

});
