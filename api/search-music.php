<?php
/**
 * Mini-X - Facebook-Style Music Search API
 * Etsii kappaleita Apple Music / iTunes Public Catalogista.
 * Palauttaa kappaleiden nimet, artistit, albumikannet ja viralliset 30-90s esikuuntelulinkit (ilman mainoksia).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Kirjaudu sisään käyttääksesi hakua.']);
    exit;
}

$term = trim($_GET['term'] ?? ($_GET['q'] ?? ''));

if (empty($term)) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

// Rajoitetaan hakusanan pituus turvallisuussyistä
$term = mb_substr($term, 0, 80, 'UTF-8');

// Rakennetaan iTunes API -pyyntö
$apiUrl = 'https://itunes.apple.com/search?term=' . urlencode($term) . '&country=TH&entity=song&limit=12';

$response = null;

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mini-X Social/1.1.2');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}

if (!$response && ini_get('allow_url_fopen')) {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 6,
            'header'  => "User-Agent: Mini-X Social/1.1.2\r\n"
        ]
    ]);
    $response = @file_get_contents($apiUrl, false, $ctx);
}

if (!$response) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Musiikkipalveluun ei saatu yhteyttä. Yritä hetken kuluttua uudelleen.']);
    exit;
}

$data = json_decode($response, true);
$results = [];

if (isset($data['results']) && is_array($data['results'])) {
    foreach ($data['results'] as $item) {
        $previewUrl = $item['previewUrl'] ?? '';
        if (empty($previewUrl)) {
            continue;
        }

        // Skaalataan kansikuva korkeampaan resoluutioon (100x100 -> 200x200)
        $artwork = $item['artworkUrl100'] ?? ($item['artworkUrl60'] ?? '');
        $artworkHighRes = str_replace('100x100bb', '200x200bb', $artwork);

        $results[] = [
            'id'          => $item['trackId'] ?? 0,
            'title'       => $item['trackName'] ?? 'Tuntematon kappale',
            'artist'      => $item['artistName'] ?? 'Tuntematon artisti',
            'album'       => $item['collectionName'] ?? '',
            'artwork'     => $artworkHighRes,
            'preview_url' => $previewUrl,
        ];
    }
}

echo json_encode([
    'success' => true,
    'query'   => $term,
    'results' => $results
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
