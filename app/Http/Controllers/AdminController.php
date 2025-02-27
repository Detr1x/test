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
class AdminController extends Controller
{
    public function showAdmin()
    {
        return view('admin.admin');
    }
    public function showTables()
    {
        $tables = Tables::withCount('columns')->get(); // Без select()

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
    public function showTableDataFillingForm($token){
        $table = Tables::where('table_token', $token)->firstOrFail();
        $columns = Columns::where('table_token', $token)->orderBy('s_number')->get();
        return view('admin.create_table_data', compact('table', 'columns'));
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

        $user = User::create([
            'user_token' => $user_token,
            'uname' => $request->uname,
            'association' => $request->association,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/admin/users');
    }
    public function create_table(Request $request)
    {
        $table_token = Str::uuid();
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $table = Tables::create([
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
            'columns.*.s_number' => 'required|integer', // Теперь обязательно передавать
        ]);



        foreach ($validated['columns'] as $index => $columnData) {
            Columns::create([
                'table_token' => $token,
                'column_token' => Str::uuid(),
                'name' => $columnData['name'],
                'type' => $columnData['type'],
                's_number' => $columnData['s_number'] ?? ($index + 1), // Если пусто, подставляем порядковый номер
            ]);
        }



        return redirect()->route('admin.create_table_data', ['token' => $token]);
    }

    public function filling_store($token, Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
        ]);

        foreach ($validated['data'] as $index => $row) {
            foreach ($row as $column_token => $value) {
                $column = Columns::where('column_token', $column_token)->first();

                Column_data::create([
                    'table_token' => $token,
                    'column_token' => $column_token,
                    'data' => $value,
                    'type' => $column->type,
                    's_number' => $index + 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Table data saved successfully!');
    }


    public function searchUsers(Request $request)
    {
        $query = $request->query('q');

        Log::info("Search query: " . $query); // Логируем запрос

        $users = User::where('id', 'like', "%$query%")
            ->orWhere('uname', 'like', "%$query%")
            ->orWhere('association', 'like', "%$query%")
            ->orWhere('role', 'like', "%$query%")
            ->limit(10)
            ->get(['id', 'uname', 'role', 'association']);

        Log::info("Found users: " . json_encode($users)); // Логируем результат

        return response()->json($users);
    }
    public function searchTables(Request $request)
    {
        $query = $request->query('q');

        Log::info("Search query: " . $query); // Логируем запрос

        $tables = Tables::where('id', 'like', "%$query%")
            ->orWhere('name', 'like', "%$query%")
            ->limit(10)
            ->get(['id', 'name']);

        Log::info("Found users: " . json_encode($tables)); // Логируем результат

        return response()->json($tables);
    }
}
