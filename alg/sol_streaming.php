<?php
// alg/sol_streaming.php

function streamProcess(string $user, int $ts, array &$state): void {
    if (!isset($state[$user])) $state[$user] = [];
    $dq =& $state[$user];
    $dq[] = $ts;
    // pop from left while window > 300s
    while ($dq[0] < $ts - 300) array_shift($dq);
}

function findUsersStreaming(array $orders): array {
    // orders: [['user'=>'A','time'=>'2025-10-18 14:00:00'], ...]
    $state = [];
    $result = [];
    foreach ($orders as $o) {
        $user = $o['user'];
        $ts = strtotime($o['time']);
        streamProcess($user, $ts, $state);
        if (count($state[$user]) >= 3) $result[$user] = true;
    }
    return array_keys($result);
}
