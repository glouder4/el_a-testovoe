<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Личный кабинет</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: #f4f6f9;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
        }
        .app-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            flex-shrink: 0;
        }
        .app-header h1 { margin: 0; font-size: 1.15rem; }
        .user { color: #666; font-size: 13px; }
        .layout {
            display: flex;
            flex: 1;
            min-height: 0;
        }
        .sidebar {
            width: 200px;
            flex-shrink: 0;
            background: #1e293b;
            color: #e2e8f0;
            padding: 16px 0;
        }
        .sidebar h2 {
            margin: 0 16px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
        }
        .sidebar button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            border: none;
            background: transparent;
            color: inherit;
            font-size: 14px;
            cursor: pointer;
        }
        .sidebar button:hover { background: #334155; }
        .sidebar button.active {
            background: #2563eb;
            color: #fff;
        }
        .main {
            flex: 1;
            min-width: 0;
            padding: 20px;
            overflow: auto;
        }
        .filters {
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            align-items: end;
        }
        .filters label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .filters input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        .filters .actions {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 4px;
        }
        .filters button[type="submit"], .filters button.secondary {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .filters button[type="submit"] { background: #2563eb; color: #fff; }
        .filters button.secondary { background: #e2e8f0; color: #334155; }
        .table-wrap {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            overflow: auto;
            max-height: calc(100vh - 220px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tr:hover td { background: #fafafa; }
        .meta-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #64748b;
        }
        .pager { display: flex; gap: 8px; align-items: center; }
        .pager button {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .pager button:disabled { opacity: .45; cursor: not-allowed; }
        .loading { color: #64748b; padding: 24px; text-align: center; }
        .error { color: #b91c1c; padding: 12px; background: #fef2f2; border-radius: 8px; margin-bottom: 12px; }
        form.inline { display: inline; }
        button.link {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
            font-size: 13px;
            padding: 0;
        }
        .top-links { display: flex; gap: 12px; align-items: center; }
        .top-links a { color: #2563eb; font-size: 13px; }
        .hidden { display: none !important; }
        .toolbar-filters {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }
        .toolbar-filters .hint { margin: 0; font-size: 13px; color: #64748b; }
        .btn-sync {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #2563eb;
            background: #fff;
            color: #2563eb;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-sync:hover { background: #eff6ff; }
        .sync-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .sync-backdrop.hidden { display: none !important; }
        .sync-modal {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 440px;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 20px 50px rgba(0,0,0,.2);
        }
        .sync-modal header {
            padding: 14px 18px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .sync-modal header h3 { margin: 0; font-size: 1rem; }
        .sync-modal .sync-body { padding: 16px 18px; }
        .sync-modal .field { margin-bottom: 12px; }
        .sync-modal .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .sync-modal .field input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        .sync-modal .field input:read-only { background: #f8fafc; color: #64748b; }
        .sync-modal .sync-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }
        .sync-modal .sync-actions button {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .sync-modal .sync-actions .primary { background: #2563eb; color: #fff; }
        .sync-modal .sync-actions .ghost { background: #e2e8f0; color: #334155; }
        .sync-modal .sync-result {
            margin-top: 14px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 12px;
            max-height: 200px;
            overflow: auto;
        }
        .sync-modal .sync-result pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
        .sync-modal .close-x {
            border: none;
            background: none;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            color: #64748b;
            padding: 0 4px;
        }
        .view-toggle {
            display: inline-flex;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .view-toggle button {
            padding: 8px 14px;
            border: none;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
        }
        .view-toggle button + button { border-left: 1px solid #e2e8f0; }
        .view-toggle button.active {
            background: #2563eb;
            color: #fff;
        }
        .toolbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }
        .analytics-panel {
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .analytics-totals {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }
        .analytics-totals .stat {
            min-width: 140px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .analytics-totals .stat .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .analytics-totals .stat .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .chart-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            background: #fafafa;
        }
        .chart-card h3 {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }
        .chart-card .chart-wrap {
            position: relative;
            height: 260px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
</head>
<body>
@php
    $cabinetApiKey = config('services.external_api.key', '');
    $cabinetToday = now()->format('Y-m-d');
@endphp
<header class="app-header">
    <h1>Личный кабинет</h1>
    <div class="top-links">
        <a href="{{ url('/tester') }}">Тестер API</a>
        <span class="user">{{ auth()->user()->name }} · {{ auth()->user()->email }}</span>
        <form class="inline" method="POST" action="{{ url('/logout') }}">
            @csrf
            <button class="link" type="submit">Выйти</button>
        </form>
    </div>
</header>
<div class="layout">
    <aside class="sidebar">
        <h2>Данные (локально)</h2>
        <button type="button" data-resource="orders" class="active">Заказы</button>
        <button type="button" data-resource="sales">Продажи</button>
        <button type="button" data-resource="stocks">Склады</button>
        <button type="button" data-resource="incomes">Доходы</button>
    </aside>
    <div class="main">
        <div id="load-error" class="error hidden"></div>
        <div class="toolbar-row">
            <div class="view-toggle" role="tablist" aria-label="Режим просмотра">
                <button type="button" id="view-table-btn" class="active" role="tab" aria-selected="true">Таблица</button>
                <button type="button" id="view-analytics-btn" role="tab" aria-selected="false">Аналитика</button>
            </div>
            <div class="toolbar-filters" style="margin-bottom:0; flex:1; justify-content:flex-end;">
                <p class="hint" style="margin:0;">Данные из локальной БД. Синхронизация с внешним API:</p>
                <button type="button" class="btn-sync" id="btn-open-sync">Синхронизировать с API</button>
            </div>
        </div>
        <form id="filters" class="filters">
            <div>
                <label for="f_date_from">Дата с</label>
                <input type="date" id="f_date_from" name="date_from">
            </div>
            <div>
                <label for="f_date_to">Дата по</label>
                <input type="date" id="f_date_to" name="date_to">
            </div>
            <div id="wrap-sync-date" class="hidden">
                <label for="f_date_from_sync">Дата выгрузки (sync)</label>
                <input type="date" id="f_date_from_sync" name="date_from_sync">
            </div>
            <div>
                <label for="f_barcode">Штрихкод</label>
                <input type="text" id="f_barcode" name="barcode" placeholder="частичное совпадение">
            </div>
            <div>
                <label for="f_warehouse">Склад</label>
                <input type="text" id="f_warehouse" name="warehouse_name" placeholder="частичное совпадение">
            </div>
            <div>
                <label for="f_article">Артикул поставщика</label>
                <input type="text" id="f_article" name="supplier_article" placeholder="частичное совпадение">
            </div>
            <div>
                <label for="f_nm_id">nm_id</label>
                <input type="text" id="f_nm_id" name="nm_id" placeholder="частичное совпадение">
            </div>
            <div id="wrap-sale-id" class="hidden">
                <label for="f_sale_id">sale_id</label>
                <input type="text" id="f_sale_id" name="sale_id">
            </div>
            <div id="wrap-income-id" class="hidden">
                <label for="f_income_id">income_id</label>
                <input type="text" id="f_income_id" name="income_id">
            </div>
            <div id="wrap-per-page">
                <label for="f_per_page">На странице</label>
                <input type="number" id="f_per_page" name="per_page" value="25" min="1" max="100">
            </div>
            <div class="actions">
                <button type="submit">Применить фильтры</button>
                <button type="button" class="secondary" id="btn-reset">Сбросить</button>
            </div>
        </form>
        <div id="panel-table">
            <div class="meta-bar">
                <span id="meta-text">Выберите раздел</span>
                <div class="pager">
                    <button type="button" id="btn-prev" disabled>Назад</button>
                    <span id="page-indicator"></span>
                    <button type="button" id="btn-next" disabled>Вперёд</button>
                </div>
            </div>
            <div class="table-wrap">
                <div id="loading" class="loading hidden">Загрузка…</div>
                <table id="data-table">
                    <thead id="thead"><tr></tr></thead>
                    <tbody id="tbody"></tbody>
                </table>
            </div>
        </div>
        <div id="panel-analytics" class="hidden">
            <div id="analytics-loading" class="loading hidden">Загрузка аналитики…</div>
            <div class="analytics-panel">
                <div id="analytics-totals" class="analytics-totals"></div>
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 id="chart-day-title">Записей по дням</h3>
                        <div class="chart-wrap"><canvas id="chart-by-day"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h3 id="chart-wh-title">Топ складов по числу записей</h3>
                        <div class="chart-wrap"><canvas id="chart-by-warehouse"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sync-backdrop" class="sync-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="sync-modal-title">
    <div class="sync-modal">
        <header>
            <h3 id="sync-modal-title">Синхронизация с API</h3>
            <button type="button" class="close-x" id="sync-close-x" aria-label="Закрыть">&times;</button>
        </header>
        <div class="sync-body">
            <form id="sync-api-form">
                <div class="field">
                    <label for="sync-key">key</label>
                    <input type="text" id="sync-key" name="key" value="{{ $cabinetApiKey }}" required autocomplete="off">
                </div>
                <div class="field">
                    <label for="sync-date-from">dateFrom</label>
                    <input type="date" id="sync-date-from" name="dateFrom">
                </div>
                <div class="field" id="sync-wrap-date-to">
                    <label for="sync-date-to">dateTo</label>
                    <input type="date" id="sync-date-to" name="dateTo">
                </div>
                <div class="field">
                    <label for="sync-page">page</label>
                    <input type="number" id="sync-page" name="page" value="1" min="1" required>
                </div>
                <div class="field">
                    <label for="sync-limit">limit</label>
                    <input type="number" id="sync-limit" name="limit" value="100" min="1" max="500" required>
                </div>
                <div class="sync-actions">
                    <button type="submit" class="primary">Запросить и сохранить</button>
                    <button type="button" class="ghost" id="sync-close-btn">Закрыть</button>
                </div>
            </form>
            <div class="sync-result hidden" id="sync-result-wrap">
                <strong>Ответ</strong>
                <pre id="sync-result-pre"></pre>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const endpoints = {
        orders: @json(url('/cabinet/data/orders')) ,
        sales: @json(url('/cabinet/data/sales')) ,
        stocks: @json(url('/cabinet/data/stocks')) ,
        incomes: @json(url('/cabinet/data/incomes')) ,
    };
    const analyticsEndpoints = {
        orders: @json(url('/cabinet/analytics/orders')) ,
        sales: @json(url('/cabinet/analytics/sales')) ,
        stocks: @json(url('/cabinet/analytics/stocks')) ,
        incomes: @json(url('/cabinet/analytics/incomes')) ,
    };
    const apiSyncUrls = {
        orders: @json(url('/api/orders')) ,
        sales: @json(url('/api/sales')) ,
        stocks: @json(url('/api/stocks')) ,
        incomes: @json(url('/api/incomes')) ,
    };
    const resourceTitles = { orders: 'Заказы', sales: 'Продажи', stocks: 'Склады', incomes: 'Доходы' };
    const cabinetToday = @json($cabinetToday);
    const columnLabels = {
        id: 'ID',
        date_from: 'Дата выгрузки',
        order_date: 'Дата заказа',
        sale_date: 'Дата продажи',
        stock_date: 'Дата остатка',
        income_date: 'Дата прихода',
        last_change_date: 'Изменено',
        g_number: 'g_number',
        sale_id: 'sale_id',
        income_id: 'income_id',
        barcode: 'Штрихкод',
        warehouse_name: 'Склад',
        supplier_article: 'Артикул',
        nm_id: 'nm_id',
        total_price: 'Сумма',
        discount_percent: 'Скидка %',
        finished_price: 'Итог цена',
        quantity: 'Кол-во',
        quantity_full: 'Полное кол-во',
        is_supply: 'Поставка',
        is_realization: 'Реализация',
        oblast: 'Область',
        date_close: 'Закрыт',
    };

    let resource = 'orders';
    let page = 1;
    let lastMeta = { last_page: 1 };
    let viewMode = 'table';
    let chartByDay = null;
    let chartByWh = null;

    const tbody = document.getElementById('tbody');
    const theadRow = document.querySelector('#thead tr');
    const metaText = document.getElementById('meta-text');
    const pageIndicator = document.getElementById('page-indicator');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const loadingEl = document.getElementById('loading');
    const loadError = document.getElementById('load-error');
    const form = document.getElementById('filters');
    const wrapSync = document.getElementById('wrap-sync-date');
    const wrapSaleId = document.getElementById('wrap-sale-id');
    const wrapIncomeId = document.getElementById('wrap-income-id');
    const wrapPerPage = document.getElementById('wrap-per-page');
    const panelTable = document.getElementById('panel-table');
    const panelAnalytics = document.getElementById('panel-analytics');
    const analyticsLoading = document.getElementById('analytics-loading');
    const analyticsTotalsEl = document.getElementById('analytics-totals');
    const btnViewTable = document.getElementById('view-table-btn');
    const btnViewAnalytics = document.getElementById('view-analytics-btn');
    const chartDayTitle = document.getElementById('chart-day-title');
    const chartWhTitle = document.getElementById('chart-wh-title');

    const syncBackdrop = document.getElementById('sync-backdrop');
    const syncModalTitle = document.getElementById('sync-modal-title');
    const syncForm = document.getElementById('sync-api-form');
    const syncDateFrom = document.getElementById('sync-date-from');
    const syncDateTo = document.getElementById('sync-date-to');
    const syncWrapDateTo = document.getElementById('sync-wrap-date-to');
    const syncResultWrap = document.getElementById('sync-result-wrap');
    const syncResultPre = document.getElementById('sync-result-pre');

    function showResourceUi() {
        wrapSync.classList.toggle('hidden', resource !== 'stocks');
        wrapSaleId.classList.toggle('hidden', resource !== 'sales');
        wrapIncomeId.classList.toggle('hidden', resource !== 'incomes');
    }

    function openSyncModal() {
        syncModalTitle.textContent = 'Синхронизация: ' + (resourceTitles[resource] || resource);
        syncResultWrap.classList.add('hidden');
        syncResultPre.textContent = '';

        const fFrom = document.getElementById('f_date_from')?.value || '';
        const fTo = document.getElementById('f_date_to')?.value || '';

        if (resource === 'stocks') {
            syncWrapDateTo.classList.add('hidden');
            syncDateFrom.value = cabinetToday;
            syncDateFrom.readOnly = true;
            syncDateTo.value = '';
            syncDateTo.removeAttribute('required');
        } else {
            syncWrapDateTo.classList.remove('hidden');
            syncDateFrom.readOnly = false;
            syncDateFrom.value = fFrom || '';
            syncDateTo.value = fTo || '';
        }

        syncBackdrop.classList.remove('hidden');
        document.getElementById('sync-key')?.focus();
    }

    function closeSyncModal() {
        syncBackdrop.classList.add('hidden');
    }

    document.getElementById('btn-open-sync')?.addEventListener('click', openSyncModal);
    document.getElementById('sync-close-x')?.addEventListener('click', closeSyncModal);
    document.getElementById('sync-close-btn')?.addEventListener('click', closeSyncModal);
    syncBackdrop?.addEventListener('click', (e) => {
        if (e.target === syncBackdrop) closeSyncModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !syncBackdrop.classList.contains('hidden')) closeSyncModal();
    });

    syncForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        syncResultWrap.classList.remove('hidden');
        syncResultPre.textContent = 'Загрузка…';

        const fd = new FormData(syncForm);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) {
            if (String(v).trim() !== '') params.append(k, String(v).trim());
        }
        if (resource === 'stocks' && !params.has('dateFrom')) {
            params.set('dateFrom', cabinetToday);
        }

        const base = apiSyncUrls[resource];
        const url = base + '?' + params.toString();

        try {
            const res = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            const ct = res.headers.get('content-type') || '';
            const body = ct.includes('application/json') ? await res.json() : await res.text();
            const text = typeof body === 'string' ? body : JSON.stringify(body, null, 2);
            syncResultPre.textContent = 'HTTP ' + res.status + ' ' + res.statusText + '\n\n' + text;
            if (res.ok) {
                refreshCurrentView();
            }
        } catch (err) {
            syncResultPre.textContent = String(err);
        }
    });

    function buildQueryString() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        params.set('page', String(page));
        const per = fd.get('per_page') || '25';
        params.set('per_page', per);
        ['date_from', 'date_to', 'barcode', 'warehouse_name', 'supplier_article', 'nm_id', 'sale_id', 'income_id', 'date_from_sync'].forEach((key) => {
            const v = fd.get(key);
            if (v !== null && String(v).trim() !== '') {
                params.set(key, String(v).trim());
            }
        });
        return params.toString();
    }

    function buildAnalyticsQueryString() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        ['date_from', 'date_to', 'barcode', 'warehouse_name', 'supplier_article', 'nm_id', 'sale_id', 'income_id', 'date_from_sync'].forEach((key) => {
            const v = fd.get(key);
            if (v !== null && String(v).trim() !== '') {
                params.set(key, String(v).trim());
            }
        });
        return params.toString();
    }

    function destroyCharts() {
        if (chartByDay) {
            chartByDay.destroy();
            chartByDay = null;
        }
        if (chartByWh) {
            chartByWh.destroy();
            chartByWh = null;
        }
    }

    function setViewMode(mode) {
        viewMode = mode;
        const isTable = mode === 'table';
        btnViewTable.classList.toggle('active', isTable);
        btnViewTable.setAttribute('aria-selected', isTable ? 'true' : 'false');
        btnViewAnalytics.classList.toggle('active', !isTable);
        btnViewAnalytics.setAttribute('aria-selected', !isTable ? 'true' : 'false');
        panelTable.classList.toggle('hidden', !isTable);
        panelAnalytics.classList.toggle('hidden', isTable);
        wrapPerPage.classList.toggle('hidden', !isTable);
        if (isTable) {
            destroyCharts();
        }
    }

    function refreshCurrentView() {
        if (viewMode === 'analytics') {
            loadAnalytics();
        } else {
            load();
        }
    }

    async function loadAnalytics() {
        if (typeof Chart === 'undefined') {
            loadError.textContent = 'Chart.js не загрузился. Проверьте сеть или CDN.';
            loadError.classList.remove('hidden');
            return;
        }
        loadError.classList.add('hidden');
        analyticsLoading.classList.remove('hidden');
        destroyCharts();

        const dayLabel = resource === 'orders' ? 'Заказы по дням'
            : resource === 'sales' ? 'Продажи по дням'
            : resource === 'stocks' ? 'Остатки по дням'
            : 'Приходы по дням';
        chartDayTitle.textContent = dayLabel;
        chartWhTitle.textContent = 'Топ складов (количество записей)';

        const url = analyticsEndpoints[resource] + '?' + buildAnalyticsQueryString();
        try {
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || ('HTTP ' + res.status));
            }

            const t = json.totals || {};
            const stats = [];
            stats.push({ label: 'Строк в выборке', value: String(t.rows ?? 0) });
            if (t.sum_total_price !== undefined) {
                stats.push({ label: 'Сумма total_price', value: String(t.sum_total_price) });
            }
            if (t.sum_quantity !== undefined) {
                stats.push({ label: 'Сумма quantity', value: String(t.sum_quantity) });
            }
            if (t.sum_quantity_full !== undefined) {
                stats.push({ label: 'Сумма quantity_full', value: String(t.sum_quantity_full) });
            }
            analyticsTotalsEl.innerHTML = stats.map((s) =>
                '<div class="stat"><div class="label">' + escapeHtml(s.label) + '</div><div class="value">' + escapeHtml(s.value) + '</div></div>'
            ).join('');

            const byDay = json.by_day || [];
            const byWh = json.by_warehouse || [];
            const dayLabels = byDay.map((d) => d.date || '');
            const dayCounts = byDay.map((d) => d.count);
            const whLabels = byWh.map((w) => w.label || '');
            const whCounts = byWh.map((w) => w.count);

            const ctxDay = document.getElementById('chart-by-day');
            const ctxWh = document.getElementById('chart-by-warehouse');
            chartByDay = new Chart(ctxDay, {
                type: 'line',
                data: {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Записей',
                        data: dayCounts,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        fill: true,
                        tension: 0.25,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { maxRotation: 45, minRotation: 0 } },
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
            chartByWh = new Chart(ctxWh, {
                type: 'bar',
                data: {
                    labels: whLabels,
                    datasets: [{
                        label: 'Записей',
                        data: whCounts,
                        backgroundColor: 'rgba(30, 41, 59, 0.75)',
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        } catch (e) {
            loadError.textContent = e.message || 'Ошибка загрузки аналитики';
            loadError.classList.remove('hidden');
            analyticsTotalsEl.innerHTML = '';
        } finally {
            analyticsLoading.classList.add('hidden');
        }
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    async function load() {
        loadError.classList.add('hidden');
        loadingEl.classList.remove('hidden');
        tbody.innerHTML = '';
        theadRow.innerHTML = '';

        const url = endpoints[resource] + '?' + buildQueryString();
        try {
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || ('HTTP ' + res.status));
            }
            const rows = json.data || [];
            lastMeta = json.meta || { last_page: 1, current_page: page, total: 0, per_page: 25, from: null, to: null };

            if (rows.length === 0) {
                metaText.textContent = 'Нет записей по фильтру';
                theadRow.innerHTML = '<th>—</th>';
                tbody.innerHTML = '<tr><td colspan="99">Пусто</td></tr>';
            } else {
                const keys = Object.keys(rows[0]);
                keys.forEach((k) => {
                    const th = document.createElement('th');
                    th.textContent = columnLabels[k] || k;
                    theadRow.appendChild(th);
                });
                rows.forEach((row) => {
                    const tr = document.createElement('tr');
                    keys.forEach((k) => {
                        const td = document.createElement('td');
                        let val = row[k];
                        if (val === null || val === undefined) val = '';
                        td.textContent = typeof val === 'object' ? JSON.stringify(val) : String(val);
                        td.title = td.textContent;
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                metaText.textContent = 'Всего: ' + (lastMeta.total ?? 0) +
                    (lastMeta.from != null ? ' · строки ' + lastMeta.from + '–' + lastMeta.to : '');
            }
            page = lastMeta.current_page || page;
            pageIndicator.textContent = 'Стр. ' + page + ' / ' + (lastMeta.last_page || 1);
            btnPrev.disabled = page <= 1;
            btnNext.disabled = page >= (lastMeta.last_page || 1);
        } catch (e) {
            loadError.textContent = e.message || 'Ошибка загрузки';
            loadError.classList.remove('hidden');
            metaText.textContent = '';
            pageIndicator.textContent = '';
            btnPrev.disabled = true;
            btnNext.disabled = true;
        } finally {
            loadingEl.classList.add('hidden');
        }
    }

    document.querySelectorAll('.sidebar button[data-resource]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sidebar button[data-resource]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            resource = btn.getAttribute('data-resource');
            page = 1;
            showResourceUi();
            refreshCurrentView();
        });
    });

    btnViewTable?.addEventListener('click', () => {
        setViewMode('table');
        load();
    });
    btnViewAnalytics?.addEventListener('click', () => {
        setViewMode('analytics');
        loadAnalytics();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        page = 1;
        refreshCurrentView();
    });

    document.getElementById('btn-reset').addEventListener('click', () => {
        form.reset();
        document.getElementById('f_per_page').value = '25';
        page = 1;
        refreshCurrentView();
    });

    btnPrev.addEventListener('click', () => {
        if (page > 1) { page--; load(); }
    });
    btnNext.addEventListener('click', () => {
        if (page < (lastMeta.last_page || 1)) { page++; load(); }
    });

    showResourceUi();
    load();
})();
</script>
</body>
</html>
