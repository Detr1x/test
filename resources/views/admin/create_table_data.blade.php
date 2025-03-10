<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>


    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Shadows+Into+Light&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">


    <link
        href="https://fonts.googleapis.com/css2?family=Inria+Sans:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Shadows+Into+Light&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Shadows+Into+Light&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet" />
    @vite([
    'resources/js/admin/user_search.js',
    'resources/sass/reset.scss',
    'resources/sass/admin.scss',
    ])
</head>

<body>
    <header>
        <h1>Welcome {{ auth()->user()->uname}}!</h1>
        <div class="nav">
            <a href="{{route('admin')}}">Dashboard</a>
            <a href="{{route('users')}}" >Users</a>
            <a href="{{route('tables')}} " style="color:#5a6ebf">Tables</a>
        </div>
        <div class="logout">
            <a href="{{route('logout')}}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                    <path fill="#475695"
                        d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z" />
                </svg>
            </a>
        </div>

    </header>
    <div class="content">
        <form action="{{ route('admin.create_table_data.store', ['token' => $table->table_token]) }}" method="POST">
    @csrf
    <div class="table_container">
        <div class="actions-bar">
            <input id="user-search-bar" type="text" placeholder="Search users..." autocomplete="off" />
            <div id="users-results" class="search-results"></div>
            <div class="btns">
                <button type="button" class="btn create" id="add-row">Add row</button>
                <button type="submit" class="btn save">Save Table</button>
            </div>
        </div>
        <table class="users_table">
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
                <tr>
                    @foreach ($columns as $column)
                        <td><input type="text" name="data[0][{{ $column->column_token }}]" data-type="{{ $column->type }}"></td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</form>
    </div>

    <script>
    document.getElementById('add-row').addEventListener('click', function () {
    let tableBody = document.getElementById('table-body');
    let rowCount = tableBody.getElementsByTagName('tr').length;
    let columns = JSON.parse(tableBody.dataset.columns);
    
    let newRow = document.createElement('tr');
    
    columns.forEach(column => {
        let newCell = document.createElement('td');
        let newInput = document.createElement('input');
        
        newInput.type = 'text';
        newInput.name = `data[${rowCount}][${column.column_token}]`;
        newInput.setAttribute('data-type', column.type);
        
        newCell.appendChild(newInput);
        newRow.appendChild(newCell);
    });
    
    tableBody.appendChild(newRow);
});

</script>
</body>

</html>
