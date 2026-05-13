<?php
/**
 * Fetch a JSON array from a partner site (used by companies/view.php).
 * Uses cURL when available; falls back to file_get_contents for local dev.
 *
 * @param string $url HTTPS/HTTP URL to a script returning a JSON array of product rows.
 * @return array<int, array<string, mixed>>|null Decoded list or null on failure.
 */
function fetch_partner_products_json($url) {
    $url = trim((string) $url);
    if ($url === '' || stripos($url, 'http') !== 0) {
        return null;
    }

    $body = null;
    $code = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OurMarketplace/1.0 (CMPE272 partner sync)');
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code !== 200) {
            $body = null;
        }
    }

    if ($body === null) {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 20,
                'header'  => "User-Agent: OurMarketplace/1.0 (CMPE272 partner sync)\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
    }

    if ($body === false || $body === null) {
        return null;
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        return null;
    }

    return $data;
}

/**
 * Normalize diverse partner payloads to the shape used by companies/view.php.
 *
 * @param array<int, array<string, mixed>> $items
 * @param string                           $apiIdPrefix Prefix for synthetic marketplace ids (e.g. 'megha', 'cookie').
 * @return array<int, array<string, mixed>>
 */
function normalize_partner_product_rows(array $items, $apiIdPrefix) {
    $prefix = preg_replace('/[^a-z0-9_-]/i', '', (string) $apiIdPrefix);
    if ($prefix === '') {
        $prefix = 'api';
    }
    $products = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $rawId = $item['id'] ?? '';
        $products[] = [
            'id'             => $prefix . '-' . (string) $rawId,
            'name'           => (string) ($item['product_name'] ?? $item['name'] ?? ''),
            'description'    => (string) ($item['description'] ?? ''),
            'price'          => (float) ($item['price'] ?? 0),
            'image_url'      => (string) ($item['image_url'] ?? $item['image'] ?? ''),
            'avg_rating'     => isset($item['avg_rating']) ? (float) $item['avg_rating'] : 0,
            'review_count'   => isset($item['review_count']) ? (int) $item['review_count'] : 0,
            'is_api_product' => true,
        ];
    }
    return $products;
}
