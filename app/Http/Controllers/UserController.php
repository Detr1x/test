<?php

namespace App\Http\Controllers;
use App\Models\Columns;
use App\Models\Column_data;
use Illuminate\Http\Request;
use App\Models\Tables;
use Dotenv\Validator as validator;
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

public function showUserTable($table_token){
    $table = Tables::where('table_token', $table_token)->firstOrFail();
    $columns = Columns::where('table_token', $table_token)->orderBy('s_number')->get();

    // Загружаем данные, сортируем по уровням, затем по родителю и номеру
    $columnData = Column_data::where('table_token', $table_token)
    ->orderByRaw("
        FIELD(hierarchy_level, 'main_header', 'header', 'sub_header', 'sub_sub_header')
    ")
    ->orderByRaw("COALESCE(parent_hierarchy_token, hierarchy_token), s_number ASC")
    ->get();


    // Группируем данные по hierarchy_token
    $groupedData = [];
    foreach ($columnData as $row) {
        if (!isset($groupedData[$row->hierarchy_token])) {
            $groupedData[$row->hierarchy_token] = [
                'hierarchy_level' => $row->hierarchy_level,
                'parent_hierarchy_token' => $row->parent_hierarchy_token,
                'type' => $row->type,
                's_number' => $row->s_number
            ];
        }
        $groupedData[$row->hierarchy_token][$row->column_token] = $row->data;
    }
    \Log::info($columnData->pluck('s_number', 'hierarchy_token')->toArray());

    return view('user_table', compact('table', 'columns', 'groupedData'));
}
public function saveCellData(Request $request) 
{
    \Log::info('Полученные данные:', $request->all());

    $request->validate([
        'table_token' => 'required|string',
        'column_token' => 'required|string',
        'hierarchy_token' => 'required|string',
        'data' => 'nullable|string',
        'type' => 'required|string',
        'hierarchy_level' => 'required|string',
        's_number' => 'required|integer',
        'method' => 'nullable|string'
    ]);

    $data = Column_Data::where('column_token', $request->column_token)
                      ->where('hierarchy_token', $request->hierarchy_token)
                      ->first();

    if ($data) {
        $data->data = $request->data;
        if ($request->has('parent_hierarchy_token')) {
            $data->parent_hierarchy_token = $request->parent_hierarchy_token;
        }
        $data->save();
    } else {
        \Log::info('Сохранение данных:', [
            'table_token' => $request->table_token,
            'column_token' => $request->column_token,
            'hierarchy_token' => $request->hierarchy_token,
            'parent_hierarchy_token' => $request->parent_hierarchy_token,
            'data' => $request->data,
            'type' => $request->type,
            'hierarchy_level' => $request->hierarchy_level,
            's_number' => $request->s_number,
            'method' => $request->method
        ]);
        
        Column_Data::create([
            'table_token' => $request->table_token,
            'column_token' => $request->column_token,
            'hierarchy_token' => $request->hierarchy_token,
            'parent_hierarchy_token' => $request->parent_hierarchy_token,
            'data' => $request->data,
            'type' => $request->type,
            'hierarchy_level' => $request->hierarchy_level,
            's_number' => $request->s_number,
            'method' => $request->method
        ]);
    }

    // Вызываем обновление родительской суммы
    $this->updateParentSum($request->table_token, $request->column_token, $request->parent_hierarchy_token);

    return response()->json(['success' => true]);
}

private function updateParentSum($table_token, $column_token, $parent_hierarchy_token)
{
    if (!$parent_hierarchy_token) return;

    // Считаем сумму всех детей для текущего родителя
    $sum = Column_Data::where('table_token', $table_token)
        ->where('column_token', $column_token)
        ->where('parent_hierarchy_token', $parent_hierarchy_token)
        ->sum('data');

    // Обновляем значение у родителя
    $parent = Column_Data::where('table_token', $table_token)
        ->where('column_token', $column_token)
        ->where('hierarchy_token', $parent_hierarchy_token)
        ->first();

    if ($parent) {
        $parent->data = $sum;
        $parent->save();
    }

    // Рекурсивно обновляем родителя текущего родителя
    if ($parent && $parent->parent_hierarchy_token) {
        $this->updateParentSum($table_token, $column_token, $parent->parent_hierarchy_token);
    }
}

}


