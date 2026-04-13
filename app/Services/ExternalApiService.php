<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ExternalApiService
{
    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function fetch(string $entity, array $query): array
    {
        $baseUrl = rtrim((string) config('services.external_api.base_url'), '/');
        $timeout = (int) config('services.external_api.timeout', 15);

        $payload = $this->normalizeQuery($query);
        $payload['key'] = (string) ($query['key'] ?? config('services.external_api.key'));

        $response = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout($timeout)
            ->get('/api/' . ltrim($entity, '/'), $payload);

        $response->throw();

        return $response->json();
    }

    private function normalizeQuery(array $query): array
    {
        $result = [];

        $result['dateFrom'] = !empty($query['dateFrom'])
            ? $query['dateFrom']
            : Carbon::now()->format('Y-m-d');

        if (!empty($query['dateTo'])) {
            $result['dateTo'] = $query['dateTo'];
        }

        $result['page'] = max(1, (int) ($query['page'] ?? 1));
        $result['limit'] = min(500, max(1, (int) ($query['limit'] ?? 500)));

        return $result;
    }
}
