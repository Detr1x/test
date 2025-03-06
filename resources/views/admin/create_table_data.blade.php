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
            <a href="{{ route('logout') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="#475695" d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9v-62.1h-128c-17.7 0-32-14.3-32-32v-64c0-17.7 14.3-32 32-32h128v-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96H96c-17.7 0-32 14.3-32 32v256c0 17.7 14.3 32 32 32h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-53 0-96-43-96-96V128C0 75 43 32 96 32h64c17.7 0 32 14.3 32 32s-14.3 32-32 32z" />
                </svg>
            </a>
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
    document.getElementById('add-row').addEventListener('click', function () {
        let tableBody = document.getElementById('table-body');
        let rowCount = tableBody.getElementsByTagName('tr').length;
        let columns = JSON.parse(tableBody.dataset.columns);

        let accessInput = document.getElementById('access-input');
        let hierarchyInput = document.getElementById('hierarchy-input');

        if (!accessInput || !hierarchyInput) {
            console.error("Access or Hierarchy input not found!");
            return;
        }

        let accessValue = accessInput.value;
        let hierarchyValue = hierarchyInput.value;

        let parentHierarchyToken = "";
        let rows = tableBody.getElementsByTagName('tr');

        for (let i = rowCount - 1; i >= 0; i--) {
            let hierarchyCell = rows[i].querySelector('[name^="data"][name$="[hierarchy]"]');
            let tokenCell = rows[i].querySelector('[name^="data"][name$="[hierarchy_token]"]');

            if (hierarchyCell && hierarchyCell.value && tokenCell) {
                let rowHierarchy = hierarchyCell.value;
                let rowToken = tokenCell.value;

                if ((hierarchyValue === "header" && rowHierarchy === "main_header") ||
                    (hierarchyValue === "sub_header" && rowHierarchy === "header") ||
                    (hierarchyValue === "sub_sub_header" && rowHierarchy === "sub_header")) {
                    parentHierarchyToken = rowToken;
                    break;
                }
            }
        }

        let newRow = document.createElement('tr');
        let hierarchyToken = `row_${Date.now()}`;

        columns.forEach(column => {
            let newCell = document.createElement('td');
            let newInput = document.createElement('input');

            newInput.type = 'text';
            newInput.name = `data[${rowCount}][${column.column_token}]`;
            newInput.setAttribute('data-type', column.type);

            newCell.appendChild(newInput);
            newRow.appendChild(newCell);
        });

        let hiddenCell = document.createElement('td');
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
});

    </script>
</body>
</html>