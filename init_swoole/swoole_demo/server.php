<?php

//创建websocket服务器对象，监听0.0.0.0:9502端口
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebsocketServer {
    public $server;
    public $connections;

    public function __construct()
    {
        $this->server = new Server("0.0.0.0", 8880);

        //监听WebSocket连接打开事件
        $this->server->on('open', function (Server $ws, Request $request) {
            $this->onOpen($ws, $request);
        });

        //监听WebSocket消息事件
        $this->server->on('message', function (Server $ws, Frame $frame) {
            $this->onMessage($ws, $frame);
        });

        //监听WebSocket连接关闭事件
        $this->server->on('close', function (Server $ws, $fd) {
            $this->onClose($ws, $fd);
        });


    }

    public function onClose(Server $ws, $fd) {
        $nickname = $this->connections[$fd]['nickname'];
        unset($this->connections[$fd]);
        $data = [
            //type 1 通知 2 记录
            'online_num' => count($this->connections),
            'online_info' => $this->getOnlineUsers()
        ];
        $this->sendToOthers($fd, 1, $nickname . ' 退出群聊', $data);
        echo "client-{$fd} is closed\n";
    }

    public function onMessage(Server $ws, Frame $frame)
    {
        $data = json_decode($frame->data, true);

        $this->sendToOne($frame->fd, $frame->fd, 3, $data['msg']);
        $this->sendToOthers($frame->fd, 2, $data['msg']);
    }

    public function onOpen(Server $ws, Request $request) {
        $nickname = $request->get['nickname'];
        $this->connections[$request->fd] = [
            'fd' => $request->fd,
            'nickname' => $nickname
        ];
        $data = [
            //type 1 通知 2 记录
            'online_num' => count($this->connections),
            'online_info' => $this->getOnlineUsers()
        ];
        $this->sendToAll($request->fd, 1,  '欢迎 ' . $nickname . ' 加入群聊', $data);
    }

    public function startServer()
    {
        $this->server->start();
    }

    public function getOnlineUsers()
    {
        $res = [];
        foreach ($this->connections as $fd => $info) {
            $res[] = [
                'label' => $info['nickname']
            ];
        }
        return $res;
    }

    /**
     * @param $from_fd  来源id
     * @param $type     类型
     * @param $msg      信息
     * @param array $data   额外数据
     */
    public function handleData($from_fd, $type, $msg, $data) {
        $send_info = [
            'type' => $type,
            'msg' => $msg,
            'nickname' => $this->connections[$from_fd]['nickname'],
            'time' => date('Y-m-d H:i'),
        ];

        if (!empty($data)) {
            $send_info = array_merge($send_info, $data);
        }
        return $send_info;
    }

    /**
     * @param $from_fd  来源id
     * @param $type     类型
     * @param $msg      信息
     * @param array $data   额外数据
     */
    public function sendToAll($from_fd, $type, $msg, $data = [])
    {
        $send_info = $this->handleData($from_fd, $type, $msg, $data);

        foreach ($this->connections as $send_fd => $v) {
            $this->server->push($send_fd, json_encode($send_info));
        }
    }

    /**
     * @param $from_fd  来源id
     * @param $type     类型
     * @param $msg      信息
     * @param array $data   额外数据
     */
    public function sendToOthers($from_fd, $type, $msg, $data = [])
    {
        $send_info = $this->handleData($from_fd, $type, $msg, $data);

        foreach ($this->connections as $send_fd => $v) {
            if ($send_fd != $from_fd) {
                $this->server->push($send_fd, json_encode($send_info));
            }
        }
    }

    /**
     * @param $from_fd  来源id
     * @param $send_to  接收id
     * @param $type     类型
     * @param $msg      信息
     * @param array $data 额外数据
     */
    public function sendToOne($from_fd, $send_to, $type, $msg, $data = [])
    {
        $send_info = $this->handleData($from_fd, $type, $msg, $data);
        $this->server->push($send_to, json_encode($send_info));

    }
}


$ws = new WebsocketServer();
$ws->startServer();

