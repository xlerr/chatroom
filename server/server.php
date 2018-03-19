<?php
// 41f0bc03a1da7114752d10c2f89e57a0

require './vendor/autoload.php';

use Predis\Client as RedisClient;
use Swoole\Process;
use Swoole\WebSocket\Server;

(new class
{
    public $server;
    public $fds = [];
    public $redis;
    public $pool = [];

    public function __construct()
    {
        $this->redis = new RedisClient([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);
        $this->server = new Server('0.0.0.0', 9501);
        $this->server->set([
            'worker_num' => 1,
        ]);
        $this->server->on('workerstart', [$this, 'onWorkerStart']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('close', [$this, 'onClose']);
        $this->server->start();
    }

    public function onClose (Server $server, $fd) {
        if (isset($this->fds[$fd])) {
            unset($this->fds[$fd]);
        }
        echo "client {$fd} closed\n";
    }

    public function onMessage(Server $server, $frame)
    {
        $this->redis->rpush('msglist', json_encode([
            'id' => $frame->fd,
            'msg' => $frame->data,
        ]));
        echo "#{$frame->fd}: " . $frame->data . PHP_EOL;
    }

    public function onOpen(Server $server, $request)
    {
        $this->fds[$request->fd] = 1;
        var_dump($this);

        echo "client #{$request->fd} connected\n";

        $this->server->push($request->fd, $request->fd);
    }

    public function onWorkerStart(Server $server, $workerId)
    {
        $process = new Process([$this, 'process'], false, true);
        $process->start();
        sleep(1);
        var_dump($process->write('aaa'));
    }

    public function process(Process $process)
    {
        swoole_event_add($process->pipe, function ($pipe) use ($process) {
            var_dump('read');
            $recv = $process->read();
            var_dump($recv);
        });

        while (1) {
            usleep(100000);
            echo "a";
            // if ($data = $this->redis->blpop('msglist', 10)) {
            //     var_dump($this);
            //     foreach ($this->fds as $fd => $_temp) {
            //         $res = $this->server->push(1, $data[1]);
            //         var_dump($res);
            //     }
            // }
        }
    }
});

