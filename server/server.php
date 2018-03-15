<?php
// 41f0bc03a1da7114752d10c2f89e57a0

use Swoole\WebSocket\Server as SWS;

$fds = [];

$server = new SWS("0.0.0.0", 9501);
$server->clients = [];
$server->set([
    'worker_num' => 1,
    // 'ssl_cert_file' => __DIR__ . '/config/ssl.crt',
    // 'ssl_key_file' => __DIR__ . '/config/ssl.key',
]);

$server->on('open', function (SWS $server, $request) use (&$fds) {
    $fds[] = $request->fd;

    $server->push($request->fd, $request->fd);
});
$server->on('message', function (SWS $server, $frame) use (&$fds) {
    $data = json_encode([
        'id' => $frame->fd,
        'msg' => $frame->data,
    ]);
    echo $frame->fd . ": " . $frame->data . PHP_EOL;
    foreach ($fds as $fd) {
        $server->push($fd, $data);
    }
});
$server->on('close', function ($server, $fd) use (&$fds) {
    $index = array_search($fd, $fds);
    if (isset($fds[$index])) {
        unset($fds[$index]);
    }
    echo "client {$fd} closed\n";
});
$server->start();
