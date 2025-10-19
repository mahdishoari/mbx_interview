<?php
require __DIR__.'/sol_sort_sliding.php';
require __DIR__.'/sol_streaming.php';

$csv = array_map('str_getcsv', file(__DIR__.'/orders.csv'));
$hdr = array_shift($csv);
$orders = [];
foreach ($csv as $row) {
    $orders[] = ['user'=>$row[0], 'time'=>$row[1]];
}

// Benchmark O(n log n)
$start = microtime(true);
$r1 = findUsers3in5($orders);
$dur1 = microtime(true) - $start;

// Benchmark Streaming
$start = microtime(true);
$r2 = findUsersStreaming($orders);
$dur2 = microtime(true) - $start;


echo "Sort+Sliding: users=" . count($r1) . ", time=" . round($dur1,4) . "s\n";
echo "Streaming:    users=" . count($r2) . ", time=" . round($dur2,4) . "s\n";


$out = fopen(__DIR__.'/bench.csv','w');
fputcsv($out,['algorithm','users','time_s']);
fputcsv($out,['sort_sliding', count($r1), round($dur1,4)]);
fputcsv($out,['streaming', count($r2), round($dur2,4)]);
fclose($out);
