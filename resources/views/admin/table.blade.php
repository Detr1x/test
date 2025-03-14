<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin panel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 20px; background: #f4f4f9; }
        .hierarchy-container { max-width: 800px; margin: auto; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .row { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #ddd; position: relative; transition: 0.3s ease-in-out; }
        .row.hidden { display: none; }
        .toggle-btn { cursor: pointer; margin-right: 10px; font-weight: bold; width: 20px; text-align: center; }
        .toggle-btn:hover { color: #007bff; }
        .cell { padding: 5px; flex-grow: 1; }
        .hierarchy-level-main_header { padding-left: 0; font-weight: 600; }
        .hierarchy-level-header { padding-left: 20px; background-color: #f8f8f8; }
        .hierarchy-level-sub_header { padding-left: 40px; background-color: #f0f0f0; }
        .hierarchy-level-sub_sub_header { padding-left: 60px; background-color: #e8e8e8; }
    </style>
</head>
<body>
    <div class="hierarchy-container">
        @foreach($groupedData as $s_number => $rows)
    @foreach($rows as $hierarchy_token => $columnsData)
        @php
            $parentToken = $columnsData['parent_hierarchy_token'] ?? null;
            $isMainHeader = ($columnsData['hierarchy_level'] === 'main_header');

<<<<<<< Updated upstream
    </header>
    <div class="content">
        <form action="{{ route('admin.create_table_data.store', ['token' => $table->table_token]) }}" method="POST">
            @csrf
            <div class="table_container">
                <div class="actions-bar">
                    <div class="btns">
                        <a href="{{route('tables')}}" type="button" class="btn create">Back</a>
                        <a href="{{route('tables')}}" type="button" class="btn create">Edit</a>
                    </div>
                </div>
                <table class="table_table" style="--columns-count: {{ count($columns) }};">
                    <thead>
                        <tr>
                            @forelse ($columns as $column)
                            <th>{{ $column->name }}</th>
                            @empty
                            <th>No columns found.</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @php
                        // Группируем данные по s_number
                        
                        $groupedData = $columns_data->groupBy('s_number');
                        @endphp
                        @forelse ($groupedData as $s_number => $dataRow)
                        <tr>
                            @foreach ($columns as $column)
                            @php
                            $cell = $dataRow->firstWhere('column_token', $column->column_token);
                            @endphp
                            <td style="{{ $dataRow->firstWhere('he', 'main_header') ? 'color:#5a6ebf' : '' }}">
                                {{ $cell->data ?? '' }}
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">Нет данных.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
=======
            // Проверяем, есть ли дочерние элементы
            $hasChildren = collect($groupedData)
                ->flatten(1)
                ->where('parent_hierarchy_token', $hierarchy_token)
                ->isNotEmpty();
        @endphp

        <div class="row hierarchy-level-{{ $columnsData['hierarchy_level'] }} 
                    {{ !$isMainHeader ? 'hidden' : '' }}" 
             data-hierarchy="{{ $hierarchy_token }}"
             data-parent="{{ $parentToken ?? '' }}">

             <span class="toggle-btn">{{ $hasChildren ? '+' : '' }}</span>

            @foreach($columns as $column)
                <span class="cell">{{ $columnsData[$column->column_token] ?? '' }}</span>
            @endforeach
        </div>
    @endforeach
@endforeach
>>>>>>> Stashed changes

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-btn").forEach(button => {
        if (button.textContent.trim() !== "") { // Проверяем, не пустая ли кнопка
            button.addEventListener("click", function () {
                const parentRow = this.closest(".row");
                const hierarchyToken = parentRow.dataset.hierarchy;
                document.querySelectorAll(`.row[data-parent='${hierarchyToken}']`).forEach(child => {
                    child.classList.toggle("hidden");
                });
                this.textContent = this.textContent === "+" ? "−" : "+";
            });
        }
    });
});

    </script>
</body>
</html>
