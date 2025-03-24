<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite([
'resources/js/app.js',
'resources/sass/app.scss',
])
</head>

<body>
    <div class="container">
        <h1>Доступные таблицы</h1>
        
        @foreach ($tables as $table)
            <div class="table-container">
                <h2>{{ $table->name }}</h2>
                <a href="{{ route('user_table', $table->table_token) }}" class="btn btn-primary">{{$table->name}}</a>
            </div>
        @endforeach
    </div>
</body>
</html>