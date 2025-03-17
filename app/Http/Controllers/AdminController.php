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

    public function showTableDataFillingForm($token)
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
                's_number' => $row->s_number
            ];
        }
        $groupedData[$row->hierarchy_token][$row->column_token] = $row->data;
    }
    \Log::info($columnData->pluck('s_number', 'hierarchy_token')->toArray());


    return view('admin.table', compact('table', 'columns', 'groupedData'));
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

            $maxSNumbers = []; // Кэш для s_number

            foreach ($validated['data'] as $row) {
                try {
                    $hierarchyLevel = $row['hierarchy_level'];

                    // Пропускаем сохранение main_header
                    if ($hierarchyLevel === 'main_header') {
                        Log::info("Пропущена строка с main_header, hierarchy_token: {$row['hierarchy_token']}");
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
                    $sNumber = ++$maxSNumbers[$hierarchyLevel];

                    foreach ($row['values'] as $column_token => $value) {
                        $column = Columns::where('column_token', $column_token)->first();
                        if (!$column) {
                            Log::warning("Пропущена колонка с token: $column_token (не найдена)");
                            continue;
                        }

                        $columnData = [
                            'table_token' => $token,
                            'column_token' => $column_token,
                            'hierarchy_token' => $hierarchyToken,
                            'parent_hierarchy_token' => $parentToken,
                            'data' => $value,
                            'type' => $column->type,
                            'hierarchy_level' => $hierarchyLevel,
                            's_number' => $sNumber,
                            'method' => $method
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

            DB::commit();
            return redirect()->route('tables')->with('success', 'Данные успешно сохранены!');
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
