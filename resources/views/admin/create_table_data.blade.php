<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< Updated upstream
    <title>Admin panel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    
    @vite([
        'resources/sass/reset.scss',
        'resources/sass/admin.scss'
    ])
</head>
<body>
    <header>
        <h1>Welcome {{ auth()->user()->uname }}!</h1>
        <nav class="nav">
            <a href="{{ route('admin') }}">Dashboard</a>
            <a href="{{ route('users') }}">Users</a>
            <a href="{{ route('tables') }}" style="color:#5a6ebf">Tables</a>
        </nav>
        <div class="logout">
            <a href="{{ route('logout') }}">&#x2716;</a>
        </div>
    </header>
    <div class="content">
        <form action="{{ route('admin.create_table_data.store', ['token' => $table->table_token]) }}" method="POST">
            @csrf
            <div class="create_column_data_container">
                <div class="actions-bar">
                    <div class="input-group">
                        <select id="access-input" class="dropdown">
                            <option value="">Select Access</option>
                            @foreach ($uniqueAccessValues as $access)
                                <option value="{{ $access }}">{{ $access }}</option>
                            @endforeach
                        </select>
                        <select id="hierarchy-input" class="dropdown">
                            <option value="">Select Hierarchy</option>
                            <option value="main_header">Main Header</option>
                            <option value="header">Header</option>
                            <option value="sub_header">Sub Header</option>
                            <option value="sub_sub_header">Sub Sub Header</option>
                        </select>
                    </div>
                    <div class="btns">
                        <button type="button" class="btn add" id="add-row">Add row</button>
                        <button type="submit" class="btn save">Save Table</button>
                    </div>
                </div>
                <table class="table_table">
                    <thead>
                        <tr>
                            @forelse ($columns as $column)
                                <th>{{ $column->name }}</th>
                            @empty
                                <th>No columns found.</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody id="table-body" data-columns='@json($columns)'>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    
    <script>
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("add-row").addEventListener("click", function () {
        let tableBody = document.getElementById("table-body");
        let rowCount = tableBody.getElementsByTagName("tr").length;
        let columns = JSON.parse(tableBody.dataset.columns);

        let accessInput = document.getElementById("access-input");
        let hierarchyInput = document.getElementById("hierarchy-input");

        let accessValue = accessInput.value;
        let hierarchyValue = hierarchyInput.value;

        let parentHierarchyToken = "";
        let rows = tableBody.getElementsByTagName("tr");

        // Поиск родительского элемента
        for (let i = rowCount - 1; i >= 0; i--) {
            let hierarchyCell = rows[i].querySelector('[name^="data"][name$="[hierarchy]"]');
            let tokenCell = rows[i].querySelector('[name^="data"][name$="[hierarchy_token]"]');

            if (hierarchyCell && hierarchyCell.value && tokenCell) {
                let rowHierarchy = hierarchyCell.value;
                let rowToken = tokenCell.value;

                if (
                    (hierarchyValue === "header" && rowHierarchy === "main_header") ||
                    (hierarchyValue === "sub_header" && rowHierarchy === "header") ||
                    (hierarchyValue === "sub_sub_header" && rowHierarchy === "sub_header")
                ) {
                    parentHierarchyToken = rowToken;
                    break;
                }
=======
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
        .hierarchy-level-sub_sub_header { padding-left: 85px; background-color: #e8e8e8; }
    </style>
</head>
<body>
    <div class="hierarchy-container">
        @foreach($groupedData as $s_number => $rows)
            @foreach($rows as $columnsData)
                @php
                    $hierarchy_token = $columnsData['hierarchy_token'];
                    $parentToken = $columnsData['parent_hierarchy_token'] ?? null;
                    $isMainHeader = ($columnsData['hierarchy_level'] === 'main_header');
                    
                    // Проверяем, есть ли у элемента дети
                    $hasChildren = collect($groupedData)
                        ->flatten(1)
                        ->where('parent_hierarchy_token', $hierarchy_token)
                        ->isNotEmpty();
                @endphp

                <div class="row hierarchy-level-{{ $columnsData['hierarchy_level'] }} 
                            {{ $parentToken ? 'hidden' : '' }}" 
                    data-hierarchy="{{ $hierarchy_token }}"
                    data-parent="{{ $parentToken ?? '' }}">

                    <span class="toggle-btn">{{ $hasChildren ? '+' : '' }}</span>

                    @foreach($columns as $column)
                        <span class="cell">{{ $columnsData[$column->column_token] ?? '' }}</span>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Функция для проверки, есть ли у строки дочерние элементы
        function hasChildren(token) {
            return document.querySelector(`.row[data-parent='${token}']`) !== null;
        }

        // Обрабатываем все строки
        document.querySelectorAll(".row").forEach(row => {
            const hierarchyToken = row.dataset.hierarchy;

            // Проверяем, есть ли у элемента дети
            if (!hasChildren(hierarchyToken)) {
                const toggleBtn = row.querySelector(".toggle-btn");
                if (toggleBtn) toggleBtn.remove(); // Удаляем кнопку, если нет детей
>>>>>>> Stashed changes
            }
        });

<<<<<<< Updated upstream
        let newRow = document.createElement("tr");
        let hierarchyToken = `row_${Date.now()}`;

        columns.forEach((column) => {
            let newCell = document.createElement("td");
            let newInput = document.createElement("input");

            // Если type === "Unit", делаем input number, иначе text
            if (column.type === "Unit") {
                newInput.type = "number";
            } else {
                newInput.type = "text";
            }

            newInput.name = `data[${rowCount}][${column.column_token}]`;
            newInput.setAttribute("data-type", column.type);
            newInput.setAttribute("data-column-token", column.column_token);
            newInput.setAttribute("autocomplete", "off"); // Отключаем автозаполнение
            newInput.value = ""; // Начальное значение пустое

            if (column.type === "Unit") {
                newInput.addEventListener("input", function () {
                    updateParentSum(newInput);
                });
                newInput.setAttribute("data-old-value", "0"); // Храним старое значение
            }

            newCell.appendChild(newInput);
            newRow.appendChild(newCell);
        });

        let hiddenCell = document.createElement("td");
        hiddenCell.innerHTML = `
            <input type="hidden" name="data[${rowCount}][access]" value="${accessValue}">
            <input type="hidden" name="data[${rowCount}][hierarchy]" value="${hierarchyValue}">
            <input type="hidden" name="data[${rowCount}][hierarchy_token]" value="${hierarchyToken}">
            <input type="hidden" name="data[${rowCount}][parent_hierarchy_token]" value="${parentHierarchyToken}">
            <input type="hidden" name="data[${rowCount}][s_number]" value="${rowCount + 1}">
        `;
        newRow.appendChild(hiddenCell);

        tableBody.appendChild(newRow);
    });

    // Функция для обновления суммы родителя
    function updateParentSum(inputElement) {
        let row = inputElement.closest("tr");
        let rowId = row.querySelector('[name$="[hierarchy_token]"]').value;
        let parentInput = row.querySelector('[name$="[parent_hierarchy_token]"]');
        let parentId = parentInput ? parentInput.value : null;
        let columnToken = inputElement.dataset.columnToken;

        let newValue = inputElement.value.trim() === "" ? 0 : parseInt(inputElement.value) || 0;
        let oldValue = parseInt(inputElement.getAttribute("data-old-value")) || 0;

        let diff = newValue - oldValue;

        // Обновляем сохранённое значение, чтобы предотвратить дублирование
        inputElement.setAttribute("data-old-value", newValue);

        if (diff === 0) return; // Если нет изменений, ничего не делаем

        // Рекурсивно обновляем родителей
        function propagateSums(parentId, columnToken, diff) {
            if (!parentId || diff === 0) return;

            let parentRow = [...document.querySelectorAll("#table-body tr")].find(row =>
                row.querySelector(`[name$="[hierarchy_token]"]`)?.value === parentId
            );

            if (!parentRow) return;

            let parentInput = parentRow.querySelector(`input[data-column-token="${columnToken}"]`);
            if (parentInput) {
                let parentValue = parseInt(parentInput.value) || 0;
                parentInput.value = parentValue + diff;
                parentInput.setAttribute("data-old-value", parentInput.value);
            }

            let grandparentId = parentRow.querySelector('[name$="[parent_hierarchy_token]"]')?.value;
            propagateSums(grandparentId, columnToken, diff);
        }

        propagateSums(parentId, columnToken, diff);
    }
});


=======
        // Добавляем обработчик кликов на кнопки `+`
        document.querySelectorAll(".toggle-btn").forEach(button => {
            button.addEventListener("click", function () {
                const parentRow = this.closest(".row");
                const hierarchyToken = parentRow.dataset.hierarchy;
                const isOpening = this.textContent === "+";

                // Функция для переключения вложенных строк
                function toggleChildren(token, show) {
                    document.querySelectorAll(`.row[data-parent='${token}']`).forEach(child => {
                        child.classList.toggle("hidden", !show);
                        if (!show) {
                            const childToken = child.dataset.hierarchy;
                            toggleChildren(childToken, false);
                            const toggleBtn = child.querySelector(".toggle-btn");
                            if (toggleBtn) toggleBtn.textContent = "+";
                        }
                    });
                }

                // Открываем/закрываем дочерние элементы
                toggleChildren(hierarchyToken, isOpening);
                this.textContent = isOpening ? "−" : "+";
            });
        });
    });
>>>>>>> Stashed changes
    </script>
</body>
</html>
