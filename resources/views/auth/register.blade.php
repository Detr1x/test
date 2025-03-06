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
    'resources/js/admin/admin.js',
    'resources/sass/reset.scss',
    'resources/sass/admin.scss',
    ])
</head>

<body>
    <header>
        <h1>Welcome {{ auth()->user()->uname}}!</h1>
        <div class="nav">
            <a href="{{route('admin')}}">Dashboard</a>
            <a href="" style="color:#5a6ebf">Users</a>
            <a href="{{route('tables')}}">Tables</a>
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

    <div class="register-container">
        <form method="POST" action="{{ route('create_user') }}">
            @csrf
            <div class="input">
                <label for="uname">Username:</label>
                <input type="text" id="uname" name="uname" required autocomplete="off">
            </div>

            <div class="input">
                <label for="association">Association:</label>
                <input type="text" id="association" name="association" readonly>
                <ul class="dropdown">
                    <li data-value="ОЛИЙ ТАЪЛИМ">ОЛИЙ ТАЪЛИМ</li>
                    <li data-value="КАСБИЙ ТАЪЛИМ">КАСБИЙ ТАЪЛИМ</li>
                    <li data-value="АКАДЕМИК ЛИЦЕЙ">АКАДЕМИК ЛИЦЕЙ</li>
                    <li data-value="ИЛМ, ФАН ВА ИННОВАЦИЯЛАР">ИЛМ, ФАН ВА ИННОВАЦИЯЛАР</li>
                    <li data-value="ИНФРАТУЗИЛМА">ИНФРАТУЗИЛМА</li>
                    <li data-value="ЎҚУВ ЖАРАЁНИНИ ВА ТАЪЛИМ СИФАТИ">ЎҚУВ ЖАРАЁНИНИ ВА ТАЪЛИМ СИФАТИ</li>
                    <li data-value="ИЛМИЙ-ТАДҚИҚОТ ФАОЛИЯТИ ">ИЛМИЙ-ТАДҚИҚОТ ФАОЛИЯТИ </li>
                    <li data-value="ХАЛҚАРО ИЛМИЙ-ТЕХНИК ҲАМКОРЛИК">ХАЛҚАРО ИЛМИЙ-ТЕХНИК ҲАМКОРЛИК</li>
                    <li data-value="МАЪНАВИЙ-МАЪРИФИЙ ИШЛАР">МАЪНАВИЙ-МАЪРИФИЙ ИШЛАР</li>
                    <li data-value="МАЛАКА ОШИРИШ">МАЛАКА ОШИРИШ</li>
                    <li data-value="ИЖРО ИНТИЗОМИ">ИЖРО ИНТИЗОМИ</li>
                </ul>
            </div>

            <div class="input">
                <label for="password">Password:</label>
                <input type="text" id="password" name="password" required autocomplete="off">
            </div>
            <button type="submit">Create</button>
        </form>
    </div>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
    console.log("JS загружен");

    const input = document.querySelector("#association");
    const dropdown = document.querySelector(".dropdown");

    if (!input || !dropdown) {
        console.log("Элементы не найдены");
        return;
    }

    // При клике на поле — показываем список
    input.addEventListener("click", function () {
        console.log("Клик по инпуту association");
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
