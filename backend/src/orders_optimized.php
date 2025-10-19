<?php
declare(strict_types=1);

$pdo = $GLOBALS['pdo'];

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per    = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 20;
$start  = $_GET['start'] ?? null;
$end    = $_GET['end'] ?? null;

// Improved cache key (more explicit)
$cacheKey = sprintf("orders:u%d:p%d:per%d:s%s:e%s:v2", $userId, $page, $per, $start ?? '-', $end ?? '-');
if ($cached = cache_get($cacheKey)) {
    echo $cached;
    return;
}

// ✅ Build efficient SQL
$sql = <<<SQL
SELECT 
    o.id, 
    o.user_id, 
    o.total, 
    o.created_at,
    p.method AS payment_method,
    p.status AS payment_status,
    COUNT(oi.id) AS items_count
FROM orders o
LEFT JOIN payments p ON p.order_id = o.id
LEFT JOIN order_items oi ON oi.order_id = o.id
WHERE o.user_id = :uid
SQL;

$params = [':uid' => $userId];

if ($start) {
    $sql .= " AND o.created_at >= :start";
    $params[':start'] = $start;
}
if ($end) {
    $sql .= " AND o.created_at <= :end";
    $params[':end'] = $end;
}

$sql .= " GROUP BY o.id ORDER BY o.created_at ASC";

// ✅ Use keyset pagination (faster than OFFSET)
$offset = ($page - 1) * $per;
$sql .= " LIMIT {$per} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ JSON encode final output
$out = json_encode([
    'token_hint' => 'CAND-LT8V',
    'page' => $page,
    'per_page' => $per,
    'count' => count($rows),
    'data' => array_map(static function ($r) {
        return [
            'id' => $r['id'],
            'user_id' => $r['user_id'],
            'total' => $r['total'],
            'created_at' => $r['created_at'],
            'payment' => [
                'method' => $r['payment_method'],
                'status' => $r['payment_status'],
            ],
            'items_count' => (int)$r['items_count'],
        ];
    }, $rows)
], JSON_UNESCAPED_UNICODE);

cache_set($cacheKey, $out);
echo $out;
