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
    public function showTableTitlesCreateForm($token)
    {
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();

        return view('admin.create_table_titles', compact('table', 'columns'));
    }

    public function showTableDataFillingForm($tableToken)
    {
        $table = Tables::where('table_token', $tableToken)->firstOrFail();

        // Загружаем столбцы, отсортированные по порядковому номеру
        $columns = Columns::where('table_token', $tableToken)
                          ->orderBy('s_number')
                          ->get();
    
        // Загружаем данные колонок с учетом иерархии
        $rows = Column_Data::where('table_token', $tableToken)
                          ->orderBy('hierarchy_level') // Сначала главные заголовки
                          ->orderBy('s_number') // Затем порядок внутри уровней
                          ->get();
    
        return view('admin.create_table_data', compact('table', 'columns', 'rows'));
    }

    public function showTable($token)
    {
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();
        $columns_data = Column_data::where('table_token', $token)->orderBy('s_number')->get();

        return view('admin.table', compact('table', 'columns', 'columns_data'));
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
            'access' => 'required|string|max:255',
        ]);
        Tables::create([
            'table_token' => $table_token,
            'name' => $request->name,
            'access' => $request->access,
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

        return redirect()->route('admin.create_table.titles', ['token' => $token]);
    }
    public function titles_store(Request $request, $tableToken)
    {
        try {
            \Log::info('Request Data: ', $request->all());
    
            $validatedData = $request->validate([
                'data' => 'required|array',
                'data.*.values' => 'required|array',
                'data.*.method' => 'required|string',
                'data.*.hierarchy_token' => 'required|string',
                'data.*.hierarchy_level' => 'required|string',
                'data.*.s_number' => 'required|integer',
                'data.*.parent_hierarchy_token' => 'nullable|string',
            ]);
    
            $processedData = [];
    
            foreach ($validatedData['data'] as $row) {
                foreach ($row['values'] as $columnToken => $dataValue) {
                    $processedData[] = [
                        'table_token' => $tableToken,  // ✅ Добавляем table_token
                        'column_token' => $columnToken,
                        'hierarchy_token' => $row['hierarchy_token'],
                        'parent_hierarchy_token' => $row['parent_hierarchy_token'],
                        'data' => $dataValue,
                        'type' => 'Unit',  // Замените на реальный тип данных, если нужно
                        'hierarchy_level' => $row['hierarchy_level'],
                        's_number' => $row['s_number'],
                        'method' => $row['method'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
    
            \Log::info('Final Processed Data: ', $processedData);
    
            // Вставляем данные в таблицу column_data
            Column_Data::insert($processedData);
    
            return redirect()->route('admin.create_table.data', ['token' => $tableToken]);
        } catch (\Exception $e) {
            \Log::error('Error in titles_store: ', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    
    
    
    public function filling_store($token, Request $request)
    {
        DB::beginTransaction();  // Начало транзакции
        $table_token = $token;
        try {
            $validated = $request->validate([
                'data' => 'required|array',
                'data.*' => 'required|array',
            ]);
            Log::info('Принятые данные:', $validated);

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
                    if (in_array($column_token, [ 'hierarchy', 'hierarchy_token', 'parent_hierarchy_token', 'table_token', 's_number'])) {
                        continue;
                    }

                    $column = Columns::where('column_token', $column_token)->first();
                    if (!$column) {
                        Log::warning("Пропущена колонка с token: $column_token (не найдена)");
                        continue;
                    }

                    $hierarchyData[$hierarchyToken]['columns'][$column_token] = [
                        'value' => $value,
                        'type' => $column->type,
                    ];
                    $hierarchyData[$hierarchyToken]['parent'] = $parentToken;
                    $hierarchyData[$hierarchyToken]['level'] = $hierarchy;
                    $hierarchyData[$hierarchyToken]['s_number'] = $index + 1;
                }
            }

            Log::info("Данные после обработки в hierarchyData:", $hierarchyData);

            // **СОХРАНЕНИЕ В БД*
            foreach ($hierarchyData as $hierarchy_token => $item) {
                foreach ($item['columns'] as $column_token => $column) {
                    if ($column['value'] !== null) {
                        Log::info('Сохраняем', [
                            'table_token' => $table_token,
                            'column_token' => $column_token,
                            'hierarchy_token' => $hierarchy_token,
                            'parent_hierarchy_token' => $item['parent'],
                            'data' => $column['value'],
                            'type' => $column['type'],
                            'hierarchy_level' => $item['level'],
                            's_number' => $item['s_number'],
                        ]);

                        $columnData = [
                            'table_token' => $table_token,
                            'column_token' => $column_token,
                            'hierarchy_token' => $hierarchy_token,
                            'parent_hierarchy_token' => $item['parent'],
                            'data' => $column['value'],
                            'type' => $column['type'],
                            'hierarchy_level' => $item['level'],
                            's_number' => $item['s_number'],
                        ];

                        Log::info("Сохраняем данные в Column_Data", $columnData);

                        // Проверка, существует ли уже такая запись
                        if (
                            !Column_Data::where('hierarchy_token', $hierarchy_token)
                                ->where('column_token', $column_token)
                                ->where('parent_hierarchy_token', $item['parent'])
                                ->exists()
                        ) {
                            Column_Data::create($columnData);
                        }
                    }
                }
            }

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
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения данных: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка сохранения данных.');
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
