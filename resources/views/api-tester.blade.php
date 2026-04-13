<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; max-width: 1000px; }
        h1 { margin-bottom: 8px; }
        .hint { color: #666; margin-bottom: 20px; }
        .layout { display: flex; gap: 16px; align-items: flex-start; }
        .forms { flex: 1; min-width: 360px; }
        .result-panel { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 16px; min-height: 280px; }
        form { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        label { display: inline-block; width: 90px; margin-bottom: 8px; }
        input { margin-bottom: 8px; padding: 6px; width: 190px; }
        button { margin-top: 8px; padding: 8px 14px; cursor: pointer; }
        .result-title { margin: 0 0 10px 0; }
        .result-meta { color: #666; margin: 0 0 10px 0; font-size: 14px; }
        pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
<h1>Тестер API</h1>
<p class="hint">Заполните параметры и отправьте GET-запрос к нужному эндпоинту.</p>

@php
    $apiKey = config('services.external_api.key');
    $today = now()->format('Y-m-d');
@endphp

<div class="layout">
    <div class="forms">
    @foreach (['sales', 'orders', 'stocks', 'incomes'] as $entity)
        <form method="GET" action="{{ '/api/' . $entity }}" class="api-form">
            <h3>/api/{{ $entity }}</h3>

            <div>
                <label for="{{ $entity }}_key">key</label>
                <input id="{{ $entity }}_key" type="text" name="key" value="{{ $apiKey }}" required>
            </div>

            <div>
                <label for="{{ $entity }}_dateFrom">dateFrom</label>
                <input
                    id="{{ $entity }}_dateFrom"
                    type="date"
                    name="dateFrom"
                    @if ($entity === 'stocks')
                        value="{{ $today }}" readonly
                    @endif
                >
            </div>

            @if ($entity !== 'stocks')
                <div>
                    <label for="{{ $entity }}_dateTo">dateTo</label>
                    <input id="{{ $entity }}_dateTo" type="date" name="dateTo">
                </div>
            @endif

            <div>
                <label for="{{ $entity }}_page">page</label>
                <input id="{{ $entity }}_page" type="number" name="page" value="1" min="1" required>
            </div>

            <div>
                <label for="{{ $entity }}_limit">limit</label>
                <input id="{{ $entity }}_limit" type="number" name="limit" value="100" min="1" max="500" required>
            </div>

            <button type="submit">Вызвать {{ $entity }}</button>
        </form>
    @endforeach
    </div>

    <div class="result-panel">
        <h3 class="result-title">Ответ сервера</h3>
        <p class="result-meta" id="result-meta">Ожидание запроса...</p>
        <pre id="result-json">{}</pre>
    </div>
</div>

<script>
    const forms = document.querySelectorAll('.api-form');
    const resultMeta = document.getElementById('result-meta');
    const resultJson = document.getElementById('result-json');

    forms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const action = form.getAttribute('action');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value !== '') {
                    params.append(key, value);
                }
            }

            const requestUrl = `${action}?${params.toString()}`;

            resultMeta.textContent = `Запрос: ${requestUrl}`;
            resultJson.textContent = 'Загрузка...';

            try {
                const response = await fetch(requestUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : await response.text();

                resultMeta.textContent = `HTTP ${response.status} ${response.statusText}`;
                resultJson.textContent = typeof payload === 'string'
                    ? payload
                    : JSON.stringify(payload, null, 2);
            } catch (error) {
                resultMeta.textContent = 'Ошибка запроса';
                resultJson.textContent = JSON.stringify({
                    message: 'Не удалось выполнить запрос',
                    error: String(error)
                }, null, 2);
            }
        });
    });
</script>

</body>
</html>
