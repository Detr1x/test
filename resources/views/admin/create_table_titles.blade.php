<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    @vite(['resources/sass/reset.scss', 'resources/sass/admin.scss'])
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
        <form action="{{ route('admin.create_table.titles_store', ['token' => $table->table_token]) }}" method="POST">
            @csrf
            <div class="create_column_data_container">
                <h2>Создание заголовков</h2>
                <div class="actions-bar">
                    <div class="input-group">
                        <select id="method-select" class="form-select">
                            <option value="" disabled selected>Выберите метод</option>
                            <option value="sum">Сумма</option>
                            <option value="average">Среднее значение</option>
                            <option value="empty">empty</option>
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
                            @foreach ($columns as $column)
                                <th>{{ $column->name }}</th>
                            @endforeach
                            <th>Метод</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" data-columns='@json($columns)'>
                        <!-- Здесь будут добавляться строки -->
                    </tbody>
                </table>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("add-row").addEventListener("click", function() {
    let tableBody = document.getElementById("table-body");
    let rowCount = tableBody.getElementsByTagName("tr").length;
    let columns = JSON.parse(tableBody.dataset.columns);
    let methodSelect = document.getElementById("method-select");
    let selectedMethod = methodSelect.value; // Получаем выбранное значение метода

    if (!selectedMethod) {
        alert("Выберите метод перед добавлением строки.");
        return;
    }

    let hierarchyToken = `row_${Date.now()}`;
    let newRow = document.createElement("tr");

    // Генерируем ячейки для данных
    columns.forEach((column) => {
        let newCell = document.createElement("td");
        let newInput = document.createElement("input");
        newInput.name = `data[${rowCount}][values][${column.column_token}]`;
        newInput.type = column.type === "Unit" ? "number" : "text";
        newInput.setAttribute("data-type", column.type);
        newInput.setAttribute("data-column-token", column.column_token);
        newInput.classList.add("form-control");

        newCell.appendChild(newInput);
        newRow.appendChild(newCell);
    });

    // **Создаём видимую ячейку для метода**
    let methodCell = document.createElement("td");
    methodCell.textContent = selectedMethod; // Отображаем метод в ячейке
    newRow.appendChild(methodCell);

    // Скрытые поля для передачи данных на сервер
    let hiddenFields = document.createElement("td");
    hiddenFields.innerHTML = `
        <input type="hidden" name="data[${rowCount}][method]" value="${selectedMethod}">
        <input type="hidden" name="data[${rowCount}][hierarchy_token]" value="${hierarchyToken}">
        <input type="hidden" name="data[${rowCount}][parent_hierarchy_token]" value="">
        <input type="hidden" name="data[${rowCount}][hierarchy_level]" value="main_header">
        <input type="hidden" name="data[${rowCount}][s_number]" value="${rowCount + 1}">
    `;
    newRow.appendChild(hiddenFields);

    // Добавляем строку в таблицу
    tableBody.appendChild(newRow);
});

      
            document.getElementById("form-submit").addEventListener("click", function(event) {
                event.preventDefault();
                let formData = new FormData(document.getElementById("data-form"));
                let structuredData = [];

                document.querySelectorAll("#table-body tr").forEach((row, index) => {
                    let rowData = {
                        values: {},
                        column_token: [],
                        type: [],
                        method: row.querySelector(`[name="data[${index}][method]"]`).value,
                        hierarchy_token: row.querySelector(
                            `[name="data[${index}][hierarchy_token]"]`).value,
                        parent_hierarchy_token: row.querySelector(
                            `[name="data[${index}][parent_hierarchy_token]"]`).value,
                        hierarchy_level: row.querySelector(
                            `[name="data[${index}][hierarchy_level]"]`).value,
                        s_number: row.querySelector(`[name="data[${index}][s_number]"]`).value,
                    };

                    row.querySelectorAll("input[data-column-token]").forEach((input) => {
                        let columnToken = input.getAttribute("data-column-token");
                        let columnType = input.getAttribute("data-type");

                        rowData.values[columnToken] = input.value || null;
                        rowData.column_token.push(columnToken);
                        rowData.type.push(columnType);
                    });

                    structuredData.push(rowData);
                });

                fetch("/save-data", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            _token: document.querySelector("[name=_token]").value,
                            data: structuredData
                        })
                    })
                    .then(response => response.json())
                    .then(data => console.log(data))
                    .catch(error => console.error(error));
            });
        });
    </script>
</body>

</html>
