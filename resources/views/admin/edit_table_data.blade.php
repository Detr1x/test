<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin panel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap">
    
    @vite([
        'resources/sass/reset.scss',
        'resources/sass/tables/table.scss',
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
            <a href="{{ route('logout') }}">Logout</a>
        </div>
    </header>
    <form action="{{route('update_table_data', ['table_token' => $table->table_token])}}" method="POST">
        @csrf
    <div class="actions-bar">
        <div class="btns">
            <button class="btn add" type="submit">Save</button>
            <a href="{{route('create_table')}}" class="btn add">Edit Columns</a>
        </div>
    </div>
    
    <div class="columns-header" style="--columns-count: {{ count($columns) }};">
        <span class="toggle-btn"></span>
        @foreach ($columns as $column)
            <span class="column-title" data-type="{{ $column->type }}">{{ $column->name }}</span>
        @endforeach
    </div>
   
    <div class="hierarchy-container" style="--columns-count: {{ count($columns) }};">
        @foreach ($groupedData as $hierarchy_token => $columnsData)
            @php
                $parentToken = $columnsData['parent_hierarchy_token'] ?? null;
                $isMainHeader = $columnsData['hierarchy_level'] === 'main_header';
                $hasChildren = collect($groupedData)->where('parent_hierarchy_token', $hierarchy_token)->isNotEmpty();
            @endphp

            <div class="row hierarchy-level-{{ $columnsData['hierarchy_level'] }} {{ !$isMainHeader ? 'hidden' : '' }}"
                data-hierarchy="{{ $hierarchy_token }}" 
                data-parent="{{ $parentToken ?? '' }}" 
                data-s_number="{{ $columnsData['s_number'] }}"
                style="--columns-count: {{ count($columns) }};">

                <span class="toggle-btn">{{ $hasChildren ? '+' : '' }}</span>

                @foreach ($columns as $column)
                    <span class="cell" data-type="{{ $column->type }}">
                        <input type="text" name="values[{{ $hierarchy_token }}][{{ $column->column_token }}]" 
                               value="{{ $columnsData[$column->column_token] ?? '' }}">
                    </span>
                @endforeach
                
                <input type="hidden" name="hierarchy_token[{{ $hierarchy_token }}]" value="{{ $hierarchy_token }}">
                <input type="hidden" name="parent_hierarchy_token[{{ $hierarchy_token }}]" value="{{ $parentToken ?? '' }}">
                <input type="hidden" name="s_number[{{ $hierarchy_token }}]" value="{{ $columnsData['s_number'] }}">
                <input type="hidden" name="hierarchy_level[{{ $hierarchy_token }}]" value="{{ $columnsData['hierarchy_level'] }}">
                <input type="hidden" name="method[{{ $hierarchy_token }}]" value="{{ $columnsData['method'] ?? 'Na' }}">
            </div>
        @endforeach
    </div>
    </form>
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
        }

        reorderHierarchy();

        document.querySelectorAll(".toggle-btn").forEach(button => {
            if (button.textContent.trim() !== "") {
                button.addEventListener("click", function () {
                    const parentRow = this.closest(".row");
                    const hierarchyToken = parentRow.dataset.hierarchy;
                    const children = document.querySelectorAll(`.row[data-parent='${hierarchyToken}']`);
                    const isOpen = [...children].some(child => !child.classList.contains("hidden"));
                    
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
    });
    </script>
</body>
</html>
