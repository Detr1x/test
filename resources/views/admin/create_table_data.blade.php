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
            }
        });

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
    </script>
</body>
</html>
