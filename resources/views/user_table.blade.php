<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin panel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap">
    
     @vite([
        'resources/sass/reset.scss',
        'resources/sass/tables/table.scss',
        ])
</head>

<body>
    <header>
        <h1>Table: {{ $table->name }}!</h1>
        <div class="logout">
            <a href="{{ route('logout') }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                <path fill="#475695"
                    d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z" />
            </svg></a>
        </div>
    </header>
    <div class="actions-bar">
        <div class="btns">
            <a href="{{route('home')}}" class="btn add">Back</a>
        </div>
    </div>
    <div class="columns-header" style="--columns-count: {{ count($columns) }};">
        <span class="toggle-btn"></span>
        @foreach ($columns as $column)
            <span class="column-title" data-type ="{{ $column->type}}">{{ $column->name }}</span>
        @endforeach
    </div>
    <div class="hierarchy-container" style="--columns-count: {{ count($columns) }};">
        @foreach ($groupedData as $hierarchy_token => $columnsData)
    @php
        $parentToken = $columnsData['parent_hierarchy_token'] ?? null;
        $isMainHeader = $columnsData['hierarchy_level'] === 'main_header';
        $hasChildren = collect($groupedData)->where('parent_hierarchy_token', $hierarchy_token)->isNotEmpty();
    @endphp

    <div class="row hierarchy-level-{{ $columnsData['hierarchy_level'] }} 
                {{ !$isMainHeader ? 'hidden' : '' }}"
        data-hierarchy="{{ $hierarchy_token }}" 
        data-parent="{{ $parentToken ?? '' }}"  
        style="--columns-count: {{ count($columns) }};">

        <span class="toggle-btn">{{ $hasChildren ? '+' : '' }}</span>

        @foreach ($columns as $column)
            @php
                $cellValue = $columnsData[$column->column_token] ?? '';
            @endphp
            <span class="cell editable-cell" 
                data-column="{{ $column->column_token }}" 
                data-hierarchy="{{ $hierarchy_token }}" 
                data-table="{{ $table->table_token }}"
                data-parent="{{ $parentToken ?? '' }}"
                data-type="{{ $column->type }}"
                data-hierarchy-level="{{ $columnsData['hierarchy_level'] }}"
                data-s-number="{{ $columnsData['s_number'] ?? '' }}"
                data-method="{{ $columnsData['method'] ?? '' }}">
                
               
                 {{ $cellValue }}
                
            </span>
        @endforeach
    </div>
@endforeach

    
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function reorderHierarchy() {
                const rows = Array.from(document.querySelectorAll(".row"));
                const container = document.querySelector(".hierarchy-container");
                const tree = {};
                const rowMap = {};
        
                rows.forEach(row => {
                    const hierarchyToken = row.dataset.hierarchy;
                    const parentToken = row.dataset.parent;
                    rowMap[hierarchyToken] = row;
        
                    if (!tree[parentToken]) {
                        tree[parentToken] = [];
                    }
                    tree[parentToken].push(row);
                });
        
                function appendChildren(parentToken) {
                    if (!tree[parentToken]) return;
                    tree[parentToken].sort((a, b) => parseInt(a.dataset.s_number, 10) - parseInt(b.dataset.s_number, 10));
        
                    tree[parentToken].forEach(child => {
                        container.appendChild(child);
                        appendChildren(child.dataset.hierarchy);
                    });
                }
        
                container.innerHTML = "";
                appendChildren("");
        
                // Перепривязываем обработчики после обновления DOM
                attachEventListeners();
            }
        
            reorderHierarchy();
        
            document.querySelectorAll(".toggle-btn").forEach(button => {
                if (button.textContent.trim() !== "") {
                    button.addEventListener("click", function () {
                        const parentRow = this.closest(".row");
                        const hierarchyToken = parentRow.dataset.hierarchy;
                        const children = document.querySelectorAll(`.row[data-parent='${hierarchyToken}']`);
        
                        const isOpen = children.length > 0 && [...children].some(child => !child.classList.contains("hidden"));
        
                        if (isOpen) {
                            hideChildren(hierarchyToken);
                            this.textContent = "+";
                        } else {
                            children.forEach(child => child.classList.remove("hidden"));
                            this.textContent = "−";
                        }
                    });
                }
            });
        
            function hideChildren(parentToken) {
                document.querySelectorAll(`.row[data-parent='${parentToken}']`).forEach(child => {
                    child.classList.add("hidden");
                    hideChildren(child.dataset.hierarchy);
                });
            }
        
            function applyDynamicStyles() {
                const mainHeaders = document.querySelectorAll(".hierarchy-level-main_header");
                let even = true;
                const colorMap = {};
        
                mainHeaders.forEach(header => {
                    const mainColor = even ? "#B4C6E7" : "#BDD7EE";
                    header.style.backgroundColor = mainColor;
                    colorMap[header.dataset.hierarchy] = mainColor;
                    even = !even;
                });
        
                document.querySelectorAll(".hierarchy-level-header").forEach(header => {
                    const parentToken = header.dataset.parent;
                    const parentColor = colorMap[parentToken];
        
                    if (parentColor === "#B4C6E7") {
                        header.style.backgroundColor = "#D9E1F2";
                    } else if (parentColor === "#BDD7EE") {
                        header.style.backgroundColor = "#DDEBF7";
                    }
        
                    colorMap[header.dataset.hierarchy] = header.style.backgroundColor;
                });
            }
        
            applyDynamicStyles();
        
            function attachEventListeners() {
    document.querySelectorAll(".editable-cell").forEach(cell => {
        cell.addEventListener("click", function () {
            if (this.querySelector("input")) return; // Уже редактируется

            let value = this.textContent.trim(); // Получаем текст из ячейки
            let input = document.createElement("input");
            input.type = "text";
            input.value = value;
            input.classList.add("inline-input");

            // Копируем dataset из ячейки в input
            for (let attr of this.attributes) {
                if (attr.name.startsWith("data-")) {
                    input.setAttribute(attr.name, attr.value);
                }
            }

            this.innerHTML = "";
            this.appendChild(input);
            input.focus();

            input.addEventListener("blur", function () { saveData(input); });
            input.addEventListener("keypress", function (e) { 
                if (e.key === "Enter") {
                    e.preventDefault(); 
                    saveData(input);
                }
            });
        });
    });
}


        
            attachEventListeners();
        
            function saveData(input) {
    let value = input.value.trim();
    let columnToken = input.dataset.column;
    let hierarchyToken = input.dataset.hierarchy;
    let tableToken = input.dataset.table;
    let parentToken = input.dataset.parent;
    let hierarchyLevel = input.dataset.hierarchyLevel;
    let sNumber = input.dataset.sNumber;
    let method = input.dataset.method;
    let type = input.dataset.type;

    if (!columnToken) {
        console.error("Ошибка: column_token не определён!", input.dataset);
        return;
    }

    // Если поле пустое, сохраняем как null (или как пустую строку, если так надо в БД)
    let formattedValue = value === "" ? null : value;

    let cell = input.parentElement;
    cell.style.backgroundColor = "#fffa90"; // Индикация сохранения

    fetch("{{ route('save_table_data') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            table_token: tableToken,
            column_token: columnToken,
            hierarchy_token: hierarchyToken,
            parent_hierarchy_token: parentToken || null,
            data: formattedValue,
            type: type,
            hierarchy_level: hierarchyLevel,
            s_number: sNumber,
            method: method || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем ячейку (если пусто, оставляем placeholder)
            cell.innerHTML = formattedValue !== null ? formattedValue : `<span class="placeholder"></span>`;
            cell.style.backgroundColor = "#c8e6c9"; // Зеленая подсветка
            setTimeout(() => { cell.style.backgroundColor = ""; }, 1000);
        } else {
            alert("Ошибка сохранения: " + data.message);
            cell.style.backgroundColor = "#ffcccc"; // Красная подсветка ошибки
        }
    })
    .catch(error => {
        console.error("Ошибка:", error);
        cell.style.backgroundColor = "#ffcccc";
    });
}




        });
        </script>
        
</body>

</html>
