<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tables;
use App\Models\Columns;
use App\Models\Column_data;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class AdminController extends Controller
{
    public function showAdmin()
    {
        return view('admin.admin');
    }

    public function showTables()
    {
        $tables = Tables::with(['columns', 'rows'])->withCount('columns')->get();
    
        // Добавляем rows_count как max(s_number)
        $tables->each(function ($table) {
            $table->rows_count = $table->rows ? $table->rows->max_rows : 0;
        });
    
        return view('admin.tables', compact('tables'));
    }
    

    public function showUsers()
    {
        $users = User::select('id', 'uname', 'association', 'role')->get();
        return view('admin.users', compact('users'));
    }

    public function showUserCreateForm()
    {
        return view('auth.register');
    }

    public function showTableCreateForm()
    {
        return view('admin.create_table');
    }

    public function showTableColumnsCreateForm($token)
    {
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();

        return view('admin.create_table_columns', compact('table', 'columns'));
    }

    public function showTableDataFillingForm($token)
    {
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();
        $uniqueAccessValues = User::pluck('association')->unique()->toArray();

<<<<<<< Updated upstream
        return view('admin.create_table_data', compact('table', 'columns', 'uniqueAccessValues'));
    }

    public function showTable($token)
=======
        return view('admin.create_table_titles', compact('table', 'columns'));
    }

    public function showTableDataFillingForm($token) 
>>>>>>> Stashed changes
    {
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();
        
        // Получаем все main_header и группируем их по s_number
        $columns_data = Column_data::where('table_token', $token)
            ->where('hierarchy_level', 'main_header')
            ->orderBy('s_number')
            ->get()
            ->groupBy('s_number');
    
        return view('admin.create_table_data', compact('table', 'columns', 'columns_data'));
    }
    
    

    public function showTable($table_token)
    {
        $table = Tables::where('table_token', $table_token)->firstOrFail();
        $columns = Columns::where('table_token', $table_token)->orderBy('s_number')->get();
        $columnData = Column_data::where('table_token', $table_token)->orderBy('s_number')->get();
    
        // Группируем данные по `s_number` и `hierarchy_token`
        $groupedData = [];
        foreach ($columnData as $row) {
            if (!isset($groupedData[$row->s_number][$row->hierarchy_token])) {
                $groupedData[$row->s_number][$row->hierarchy_token] = [
                    'hierarchy_level' => $row->hierarchy_level,
                    'parent_hierarchy_token' => $row->parent_hierarchy_token, // <---- Добавлено!
                ];
            }
            $groupedData[$row->s_number][$row->hierarchy_token][$row->column_token] = $row->data;
        }
    
        return view('admin.table', compact('table', 'columns', 'columnData', 'groupedData'));
    }
    
    
    

    
    public function create_user(Request $request)
    {
        $user_token = Str::uuid();
        $request->validate([
            'uname' => 'required|string|max:255',
            'association' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        User::create([
            'user_token' => $user_token,
            'uname' => $request->uname,
            'association' => $request->association,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/admin/users')->with('success', 'Пользователь успешно создан!');
    }

    public function create_table(Request $request)
    {
        $table_token = Str::uuid();
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Tables::create([
            'table_token' => $table_token,
            'name' => $request->name,
        ]);

        return redirect()->route('admin.create_table.columns', ['token' => $table_token]);
    }

    public function columns_store($token, Request $request)
    {
        
        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*.name' => 'required|string|max:255',
            'columns.*.type' => 'required|string|max:255',
            'columns.*.s_number' => 'required|integer',
        ]);
        
        foreach ($validated['columns'] as $index => $columnData) {
            Columns::create([
                'table_token' => $token,
                'column_token' => Str::uuid(),
                'name' => $columnData['name'],
                'type' => $columnData['type'],
                's_number' => $columnData['s_number'],
            ]);
        }

        return redirect()->route('admin.create_table_data', ['token' => $token]);
    }

    public function filling_store($token, Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'data' => 'required|array',
                'data.*.values' => 'required|array',
                'data.*.method' => 'required|string',
                'data.*.hierarchy_token' => 'required|string',
                'data.*.hierarchy_level' => 'required|string',
                'data.*.parent_hierarchy_token' => 'required|string',
            ]);
            Log::info('Принятые данные:', $validated);
<<<<<<< Updated upstream

            $hierarchyMap = [];
            $hierarchyData = [];

            foreach ($validated['data'] as $index => $row) {
                $access = $row['access'] ?? null;
                $hierarchy = $row['hierarchy'] ?? null;
                $parentToken = $row['parent_hierarchy_token'] ?? null;

                if ($parentToken && isset($hierarchyMap[$parentToken])) {
                    $parentToken = $hierarchyMap[$parentToken];
                }

                if (!isset($hierarchyMap[$row['hierarchy_token']])) {
                    $hierarchyToken = (string) Str::uuid();
                    $hierarchyMap[$row['hierarchy_token']] = $hierarchyToken;
                } else {
                    $hierarchyToken = $hierarchyMap[$row['hierarchy_token']];
                }

                foreach ($row as $column_token => $value) {
                    if (in_array($column_token, ['access', 'hierarchy', 'hierarchy_token', 'parent_hierarchy_token', 'table_token', 's_number'])) {
=======
    
            $maxSNumbers = []; // Кэш для s_number
    
            foreach ($validated['data'] as $row) {
                try {
                    $hierarchyLevel = $row['hierarchy_level'];
    
                    // Пропускаем сохранение main_header
                    if ($hierarchyLevel === 'main_header') {
                        Log::info("Пропущена строка с main_header, hierarchy_token: {$row['hierarchy_token']}");
>>>>>>> Stashed changes
                        continue;
                    }
    
                    $hierarchyToken = $row['hierarchy_token'];
                    $parentToken = $row['parent_hierarchy_token'] ?? null;
                    $method = $row['method'];
    
                    // Определяем s_number один раз для данного уровня
                    if (!isset($maxSNumbers[$hierarchyLevel])) {
                        $maxSNumbers[$hierarchyLevel] = Column_Data::where('table_token', $token)
                            ->where('hierarchy_level', $hierarchyLevel)
                            ->max('s_number') ?? 0;
                    }
<<<<<<< Updated upstream

                    $hierarchyData[$hierarchyToken]['columns'][$column_token] = [
                        'value' => $value,
                        'type' => $column->type,
                    ];
                    $hierarchyData[$hierarchyToken]['parent'] = $parentToken;
                    $hierarchyData[$hierarchyToken]['level'] = $hierarchy;
                    $hierarchyData[$hierarchyToken]['s_number'] = $index + 1;
                    $hierarchyData[$hierarchyToken]['access'] = $access;
                }
            }

            Log::info("Данные после обработки в hierarchyData:", $hierarchyData);

            // **СОХРАНЕНИЕ В БД*
            foreach ($hierarchyData as $hierarchy_token => $item) {
                foreach ($item['columns'] as $column_token => $column) {
                    if ($column['value'] !== null) {
                        Log::info('Cохраняем', [
                            'table_token' => $table_token,
                            'column_token' => $column_token,
                            'hierarchy_token' => $hierarchy_token,
                            'parent_hierarchy_token' => $item['parent'],
                            'data' => $column['value'],
                            'type' => $column['type'],
                            'access' => $item['access'],
                            'hierarchy_level' => $item['level'],
                            's_number' => $item['s_number'],
                        ]);

=======
                    $sNumber = ++$maxSNumbers[$hierarchyLevel];
    
                    foreach ($row['values'] as $column_token => $value) {
                        $column = Columns::where('column_token', $column_token)->first();
                        if (!$column) {
                            Log::warning("Пропущена колонка с token: $column_token (не найдена)");
                            continue;
                        }
    
>>>>>>> Stashed changes
                        $columnData = [
                            'table_token' => $token,
                            'column_token' => $column_token,
<<<<<<< Updated upstream
                            'hierarchy_token' => $hierarchy_token,
                            'parent_hierarchy_token' => $item['parent'],
                            'data' => $column['value'],
                            'type' => $column['type'],
                            'access' => $item['access'],
                            'hierarchy_level' => $item['level'],
                            's_number' => $item['s_number'],
=======
                            'hierarchy_token' => $hierarchyToken,
                            'parent_hierarchy_token' => $parentToken,
                            'data' => $value,
                            'type' => $column->type,
                            'hierarchy_level' => $hierarchyLevel,
                            's_number' => $sNumber,
                            'method' => $method
>>>>>>> Stashed changes
                        ];
    
                        Log::info("Сохраняем данные в Column_Data", $columnData);
    
                        Column_Data::updateOrCreate(
                            [
                                'hierarchy_token' => $hierarchyToken,
                                'column_token' => $column_token
                            ],
                            $columnData
                        );
                    }
                } catch (\Exception $e) {
                    Log::error("Ошибка обработки строки с hierarchy_token: $hierarchyToken. " . $e->getMessage());
                    continue; // Пропустить проблемную строку, но не прерывать всю операцию
                }
            }
<<<<<<< Updated upstream

            // Обработка дочерних элементов
            foreach ($hierarchyData as $hierarchy_token => $item) {
                foreach ($hierarchyData as $child_hierarchy_token => $child_item) {
                    if ($child_item['parent'] === $hierarchy_token) {
                        foreach ($child_item['columns'] as $column_token => $column) {
                            if ($column['value'] !== null) {
                                $columnData = [
                                    'table_token' => $table_token,
                                    'column_token' => $column_token,
                                    'hierarchy_token' => $child_hierarchy_token,
                                    'parent_hierarchy_token' => $hierarchy_token,
                                    'data' => $column['value'],
                                    'type' => $column['type'],
                                    'access' => $child_item['access'],
                                    'hierarchy_level' => $child_item['level'],
                                    's_number' => $child_item['s_number'],
                                ];

                                Log::info("Сохраняем данные в Column_Data", $columnData);

                                // Проверка, существует ли уже такая запись
                                if (
                                    !Column_Data::where('hierarchy_token', $child_hierarchy_token)
                                        ->where('column_token', $column_token)
                                        ->where('parent_hierarchy_token', $hierarchy_token)
                                        ->exists()
                                ) {
                                    Column_Data::create($columnData);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();  // Завершаем транзакцию

            return redirect('admin/tables')->with('success', 'Данные успешно сохранены!');
=======
    
            DB::commit();
            return redirect()->route('tables')->with('success', 'Данные успешно сохранены!');
>>>>>>> Stashed changes
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка сохранения данных: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors('Ошибка сохранения данных!');
        }
    }
    






    public function searchUsers(Request $request)
    {
        $query = trim($request->query('q'));

        if (empty($query)) {
            return response()->json([]);
        }

        Log::info("Поиск пользователей по запросу: " . $query);

        $users = User::where('id', 'like', "%$query%")
            ->orWhere('uname', 'like', "%$query%")
            ->orWhere('association', 'like', "%$query%")
            ->orWhere('role', 'like', "%$query%")
            ->limit(10)
            ->get(['id', 'uname', 'role', 'association']);

        return response()->json($users);
    }

    public function searchTables(Request $request)
    {
        $query = trim($request->query('q'));

        if (empty($query)) {
            return response()->json([]);
        }

        Log::info("Поиск таблиц по запросу: " . $query);

        $tables = Tables::where('id', 'like', "%$query%")
            ->orWhere('name', 'like', "%$query%")
            ->withCount('columns')
            ->limit(10)
            ->get(['id', 'name', 'table_token']);

        return response()->json($tables);
    }
    public function getHierarchy(Request $request)
    {
        $level = $request->query('level');

        if ($level === "header") {
            $hierarchies = Column_data::where('hierarchy_level', 'Main_header')->get(['hierarchy_token', 'hierarchy']);
        } elseif ($level === "sub_header") {
            $hierarchies = Column_data::where('hierarchy_level', 'header')->get(['hierarchy_token', 'hierarchy']);
        } elseif ($level === "sub_sub_header") {
            $hierarchies = Column_data::where('hierarchy_level', 'sub_header')->get(['hierarchy_token', 'hierarchy']);
        } else {
            return response()->json([]);
        }

        return response()->json($hierarchies);
    }

}
