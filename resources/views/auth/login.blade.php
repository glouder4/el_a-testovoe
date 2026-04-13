<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — личный кабинет</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f9;
            color: #1a1a1a;
        }
        .card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,.08);
        }
        h1 { margin: 0 0 8px; font-size: 1.35rem; }
        .hint { color: #666; font-size: 14px; margin-bottom: 24px; }
        label { display: block; font-size: 14px; margin-bottom: 6px; font-weight: 500; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 16px;
        }
        input:focus { outline: none; border-color: #3b82f6; }
        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 14px; }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .errors { color: #b91c1c; font-size: 13px; margin-top: -12px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Вход</h1>
    <p class="hint">Войдите, чтобы открыть личный кабинет и тестер API.</p>

    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

        @error('email')
            <div class="errors">{{ $message }}</div>
        @enderror

        <label for="password">Пароль</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <label class="remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Запомнить меня
        </label>

        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>
