<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite('resources/js/app.js')
</head>
<body>
<div>
    <div>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{route("main.index")}}">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route("post.index")}}">Посты</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route("about.index")}}">Общая</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="{{route("contact.index")}}">Контакты</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>

    @yield("content")
</body>
</html>
