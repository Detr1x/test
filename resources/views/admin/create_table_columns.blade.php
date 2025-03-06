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
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000&family=Shadows+Into+Light&display=swap"
        rel="stylesheet">
    @vite([
    'resources/js/admin/columns.js',
    'resources/sass/reset.scss',
    'resources/sass/admin.scss',
    ])
</head>

<body>
    <header>
        <h1>Welcome {{ auth()->user()->uname}}!</h1>
        <div class="nav">
            <a href="{{route('admin')}}">Dashboard</a>
            <a href="{{route( 'users')}}">Users</a>
            <a href="{{route('tables')}} #475695  " style="color:#5a6ebf">Tables</a>
        </div>
        <div class="logout">
            <a href="{{route('logout')}}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="#475695"
                        d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z" />
                </svg>
            </a>
        </div>
    </header>
    <div class="create_columns_container">
    <form method="POST" action="{{ route('admin.create_table.columns_store', ['token' => $table->table_token]) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $table->table_token }}">

        <div class="input">
            <label for="name">Name:</label>
            <input type="text" id="name" autocomplete="off">
        </div>
        <div class="input">
            <label for="type">Type:</label>
            <input type="text" id="type" autocomplete="off">
             <ul class="dropdown">
                    <li data-value="Unit">Unit</li>
                    <li data-value="Data">Data</li>
                    <li data-value="Date">Date</li>
                    <li data-value="Comparing">Comparing</li>
                    <li data-value="Serial numbers">Serial numbers</li>
                </ul>
        </div>

        <div class="columns_show"></div>

        <button type="button" id="addColumn">Add Column</button>
        <button type="submit">Confirm</button>
    </form>
    
    <h3>Table Preview</h3>
    <table class="table-preview">
        <thead>
            <tr></tr>
        </thead>
    </table>
</div>

<script>
     document.addEventListener("DOMContentLoaded", function () {
    console.log("JS загружен");

    const input = document.querySelector("#type");
    const dropdown = document.querySelector(".dropdown");

    if (!input || !dropdown) {
        console.log("Элементы не найдены");
        return;
    }

    // При клике на поле — показываем список
    input.addEventListener("click", function () {
        console.log("Клик по инпуту type");
        dropdown.style.display = "block";
    });

    // Выбор элемента из списка
    dropdown.addEventListener("click", function (event) {
        if (event.target.tagName === "LI") {
            input.value = event.target.getAttribute("data-value");
            dropdown.style.display = "none";
        }
    });

    // Закрытие при клике вне списка и инпута
    document.addEventListener("click", function (event) {
        if (!input.contains(event.target) && !dropdown.contains(event.target)) {
            console.log("Клик вне, скрываем список");
            dropdown.style.display = "none";
        }
    });
});

    </script>

</body>

</html>
