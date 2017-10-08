<?php
// init ws
$ws = new swoole_websocket_server('0.0.0.0', 8052);

// 以守护模式启动
$ws->set(array(
    'daemonize' => true
));

$ws->fds = [];

// open
$ws->on('open', function ($serv, $req) {
    $serv->fds[] = $req->fd;
    $serv->push($req->fd, '{"msg" : "hello world"}');
});

// receive msg
$ws->on('message', function ($serv, $frame) {
    $msg = '{"fd": "' . $frame->fd . '" , "msg" : "' . $frame->data . '"}';
    foreach ($serv->fds as $fd) {
        $serv->push($fd, $msg);
    }
});

// close
$ws->on('close', function ($serv, $fd) {
    $serv->push($fd, '{"msg" : "closed"}');
    unset($serv->fds[$fd - 1]);
});

$ws->start();
