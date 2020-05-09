<?php

//创建websocket服务器对象，监听0.0.0.0:9502端口
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$ws = new Server("0.0.0.0", 8880);

//监听WebSocket连接打开事件
$ws->on('open', function (Server $ws, Request $request) {
//    $ws->push($request->fd, json_encode($request->get['nickname']));
    $nickname = $request->get['nickname'];
    $data = [
        //type 1 通知 2 记录
        'type' => 1,
        'msg' => '欢迎 ' . $nickname . ' 加入群聊',
        'time' => date('Y-m-d H:i:s'),
    ];
    foreach ($ws->connection_list() as $fd) {
        $ws->push($fd, json_encode($data));
    }
//    var_dump($request->fd, $request->get, $request->server);
//    $ws->push($request->fd, "hello, welcome\n");
});

//监听WebSocket消息事件
$ws->on('message', function (Server $ws, Frame $frame) {
    $data = json_decode($frame->data, true);

    $send = [
        //type 1 通知 2 接收 3 发送
        'msg' => $data['msg'],
        'nickname' => $data['nickname'],
        'time' => date('Y-m-d H:i:s'),
    ];
    foreach ($ws->connection_list() as $fd) {
        $send['type'] = $fd == $frame->fd ? 3 : 2;
        $ws->push($fd, json_encode($send));
        echo 'send to ' . $fd . ' ' . $send['msg'] . PHP_EOL;
    }
//    echo "Message: {$frame->data}\n";
//    $ws->push($frame->fd, "server: {$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('close', function (Server $ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();