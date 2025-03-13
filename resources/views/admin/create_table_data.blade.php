<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .btn {
            cursor: pointer;
            padding: 5px 10px;
            margin: 2px;
        }
        .add-row {
            background-color: #5cb85c;
            color: white;
            border: none;
        }
        .remove-row {
            background-color: #d9534f;
            color: white;
            border: none;
        }
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
    
    <form action="{{  route('admin.create_table.titles_store', ['token' => $table->table_token])}}" method="POST">
        @csrf
        <table class="table_table">
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
                @foreach ($rows as $row)
                    @php
                        $rowData = is_array($row->data) ? $row->data : json_decode($row->data, true) ?? [];
                    @endphp
                    <tr class="hierarchy-row" 
                        data-hierarchy-level="{{ $row->hierarchy_level }}" 
                        data-hierarchy-token="{{ $row->hierarchy_token }}" 
                        data-parent-token="{{ $row->parent_hierarchy_token ?? '' }}"
                        style="padding-left: {{ 20 * (int) $row->hierarchy_level }}px;">

                        <td>
                            @if ($row->hierarchy_level !== 'sub_sub_header')
                                <button type="button" class="btn add-row">+</button>
                            @endif
                        </td>

                        @foreach ($columns as $column)
                            <td>
                                <input type="text" 
                                       name="data[{{ $row->id }}][values][{{ $column->column_token }}]" 
                                       value="{{ $rowData[$column->column_token] ?? '' }}"
                                       data-column-token="{{ $column->column_token }}">
                            </td>
                        @endforeach

                        <td>{{ $row->method }}</td>
                        <td><button type="button" class="btn remove-row">-</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn save">Сохранить</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let tableBody = document.getElementById("table-body");

        function getNextLevel(level) {
            const hierarchyOrder = ['main_header', 'header', 'sub_header', 'sub_sub_header'];
            let currentIndex = hierarchyOrder.indexOf(level);
            return currentIndex !== -1 && currentIndex < hierarchyOrder.length - 1 ? hierarchyOrder[currentIndex + 1] : null;
        }

        function applyPadding(row, level) {
            let paddingLeft = 20 * getHierarchyIndex(level);
            row.style.paddingLeft = `${paddingLeft}px`;
        }

        function getHierarchyIndex(level) {
            let hierarchyOrder = ['main_header', 'header', 'sub_header', 'sub_sub_header'];
            return hierarchyOrder.indexOf(level);
        }

        function insertAfter(referenceNode, newNode) {
            if (referenceNode.nextSibling) {
                referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
            } else {
                referenceNode.parentNode.appendChild(newNode);
            }
        }

        function addRowListeners(row) {
            row.querySelector(".add-row")?.addEventListener("click", function() {
                let parentRow = this.closest("tr");
                let parentLevel = parentRow.dataset.hierarchyLevel;
                let parentToken = parentRow.dataset.hierarchyToken;
                let newLevel = getNextLevel(parentLevel);
                if (!newLevel) return;

                let hierarchyToken = `row_${Date.now()}`;
                let newRow = document.createElement("tr");
                newRow.classList.add("hierarchy-row");
                newRow.dataset.hierarchyLevel = newLevel;
                newRow.dataset.hierarchyToken = hierarchyToken;
                newRow.dataset.parentToken = parentToken;

                let plusButton = newLevel !== 'sub_sub_header' ? `<button type="button" class="btn add-row">+</button>` : '';

                let columnsHtml = `@foreach ($columns as $column)
                        <td><input type="text" name="data[new_${hierarchyToken}][values][{{ $column->column_token }}]"></td>
                    @endforeach`;

                newRow.innerHTML = `
                    <td>${plusButton}</td>
                    ${columnsHtml}
                    <td>
                        <select name="data[new_${hierarchyToken}][method]">
                            <option value="sum">Сумма</option>
                            <option value="average">Среднее</option>
                            <option value="Na">Na</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn remove-row">-</button></td>
                `;

                insertAfter(parentRow, newRow);
                applyPadding(newRow, newLevel);
                addRowListeners(newRow);
            });

            row.querySelector(".remove-row")?.addEventListener("click", function() {
                let row = this.closest("tr");
                let hierarchyToken = row.dataset.hierarchyToken;
                
                document.querySelectorAll(`.hierarchy-row[data-parent-token='${hierarchyToken}']`).forEach(childRow => {
                    childRow.remove();
                });

                row.remove();
            });
        }

        document.querySelectorAll(".hierarchy-row").forEach(addRowListeners);
    });
</script>

</body>
</html>
