<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        .btn { cursor: pointer; padding: 5px 10px; margin: 2px; }
        .add-row { background-color: #5cb85c; color: white; border: none; }
        .remove-row { background-color: #d9534f; color: white; border: none; }
        .hierarchy-row td:first-child { position: relative; padding-left: 0px; }
        .level-header td:first-child { padding-left: 20px; }
        .level-sub_header td:first-child { padding-left: 40px; }
        .level-sub_sub_header td:first-child { padding-left: 60px; }
    </style>
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
    <h2>Создание структуры данных</h2>

    <form id="saveForm" action="{{ route('admin.create_table.data_store', ['token' => $table->table_token]) }}" method="POST">
        @csrf
        <table id="data-table">
            <thead>
                <tr>
                    <th></th> <!-- Кнопка "+" -->
                    @foreach ($columns as $column)
                        <th>{{ $column->name }}</th>
                    @endforeach
                    <th>Метод</th>
                    <th></th> <!-- Кнопка "-" -->
                </tr>
            </thead>
            <tbody id="table-body">
                @foreach ($columns_data as $s_number => $rows)
                    <tr class="hierarchy-row" 
                        data-hierarchy-level="main_header"
                        data-hierarchy-token="{{ $rows->first()->hierarchy_token }}"
                        data-parent-token="">
                        <td><button type="button" class="btn add-row">+</button></td>
                        
                        @foreach ($columns as $column)
                            <td>
                                <input type="text" value="{{ optional($rows->firstWhere('column_token', $column->column_token))->data }}" disabled>
                            </td>
                        @endforeach

                        <td>{{ $rows->first()->method }}</td>
                        <td></td> <!-- "-" для main_header убран -->
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn save">Сохранить</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".add-row").forEach((button) => {
            button.addEventListener("click", function () {
                addRow(this);
            });
        });
    
        document.querySelectorAll(".remove-row").forEach((button) => {
            button.addEventListener("click", function () {
                removeRow(this.closest("tr"));
            });
        });
    });
    
    // Генерация уникального токена
    function generateToken() {
        return 'token-' + Math.random().toString(36).substr(2, 9);
    }
    
    // Добавление строки
    function addRow(button) {
        let parentRow = button.closest("tr");
        let table = document.querySelector("#table-body");
    
        if (!parentRow || !table) {
            console.error("Ошибка: родительская строка или таблица не найдены.");
            return;
        }
    
        let parentHierarchyToken = parentRow.dataset.hierarchyToken;
        let currentLevel = parentRow.dataset.hierarchyLevel;
        let newHierarchyToken = generateToken();
        let newLevel = getNextHierarchyLevel(currentLevel);
    
        if (!newLevel) return; // Не добавляем, если превышен уровень
    
        let newSNumber = getNextSNumber(parentHierarchyToken, newLevel);
        let newRow = generateRowHtml(newHierarchyToken, parentHierarchyToken, newLevel, newSNumber);
    
        insertRowBelowHierarchy(newRow, parentRow, table);
    }
    
    // Определение следующего уровня
    function getNextHierarchyLevel(currentLevel) {
        switch (currentLevel) {
            case "main_header": return "header";
            case "header": return "sub_header";
            case "sub_header": return "sub_sub_header";
            default: return null;
        }
    }
    
    // Получение нового `s_number`
    function getNextSNumber(parentHierarchyToken, level) {
        let rows = document.querySelectorAll(`tr[data-hierarchy-level="${level}"][data-parent-token="${parentHierarchyToken}"]`);
        return rows.length + 1;
    }
    
    // Генерация HTML строки
    function generateRowHtml(hierarchyToken, parentHierarchyToken, level, sNumber) {
        let newRow = document.createElement("tr");
        newRow.classList.add("hierarchy-row");
        newRow.dataset.hierarchyToken = hierarchyToken;
        newRow.dataset.parentToken = parentHierarchyToken;
        newRow.dataset.hierarchyLevel = level;
        newRow.dataset.sNumber = sNumber;
    
        let indent = getIndent(level);
    
        let columnsHtml = "";
        @foreach ($columns as $column)
            columnsHtml += `<td><input type="text" name="data[${hierarchyToken}][values][{{ $column->column_token }}]"></td>`;
        @endforeach
    
        // Убираем кнопку "+" для sub_sub_header
        let addButtonHtml = level !== "sub_sub_header" ? `<button type="button" class="btn add-row">+</button>` : "";
    
        newRow.innerHTML = `
            <td style="padding-left: ${indent}px;">
                ${addButtonHtml}
            </td>
            ${columnsHtml}
            <td>
                <select name="data[${hierarchyToken}][method]" required>
                    <option value="sum">Сумма</option>
                    <option value="average">Среднее</option>
                </select>
            </td>
            <td><button type="button" class="btn remove-row">-</button></td>
    
            <input type="hidden" name="data[${hierarchyToken}][hierarchy_token]" value="${hierarchyToken}">
            <input type="hidden" name="data[${hierarchyToken}][parent_hierarchy_token]" value="${parentHierarchyToken}">
            <input type="hidden" name="data[${hierarchyToken}][hierarchy_level]" value="${level}">
            <input type="hidden" name="data[${hierarchyToken}][s_number]" value="${sNumber}">
        `;
    
        if (level !== "sub_sub_header") {
            newRow.querySelector(".add-row").addEventListener("click", function () {
                addRow(this);
            });
        }
    
        newRow.querySelector(".remove-row").addEventListener("click", function () {
            removeRow(newRow);
        });
    
        return newRow;
    }
    
    // Вставка строки **после всей текущей иерархии**
    function insertRowBelowHierarchy(newRow, parentRow, table) {
        let lastChild = findLastChildRow(parentRow);
    
        if (lastChild) {
            lastChild.after(newRow);
        } else {
            parentRow.after(newRow);
        }
    }
    
    // Поиск последнего потомка в иерархии
    function findLastChildRow(parentRow) {
        let parentHierarchyToken = parentRow.dataset.hierarchyToken;
        let allRows = Array.from(document.querySelectorAll("tr"));
        
        let lastChild = null;
        allRows.forEach(row => {
            if (row.dataset.parentToken === parentHierarchyToken || isDescendant(row, parentHierarchyToken)) {
                lastChild = row;
            }
        });
    
        return lastChild;
    }
    
    // Проверка, является ли строка потомком родителя
    function isDescendant(row, parentHierarchyToken) {
        let currentParentToken = row.dataset.parentToken;
        while (currentParentToken) {
            if (currentParentToken === parentHierarchyToken) return true;
            let parentRow = document.querySelector(`tr[data-hierarchy-token="${currentParentToken}"]`);
            if (!parentRow) break;
            currentParentToken = parentRow.dataset.parentToken;
        }
        return false;
    }
    
    // Удаление строки (с удалением всех потомков)
    function removeRow(row) {
        if (!row) return;
        let hierarchyToken = row.dataset.hierarchyToken;
        document.querySelectorAll(`tr[data-parent-token="${hierarchyToken}"]`).forEach(child => removeRow(child));
        row.remove();
    }
    
    // Отступ в зависимости от уровня
    function getIndent(level) {
        return { "header": 20, "sub_header": 40, "sub_sub_header": 60 }[level] || 0;
    }
    </script>
    
    
    

</body>
</html>
