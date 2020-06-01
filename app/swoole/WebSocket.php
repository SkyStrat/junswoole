<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/28
 * Time: 17:15
 */

namespace app\swoole;



use app\Util\driver\Redis;

class WebSocket
{
    private $ws;
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
        $config = config();
        $this->ws = new \swoole_websocket_server($config['websocket']['ip'], $config['websocket']['port']);
        $this->ws->set($config['set']);
        $this->ws->on('open',  [$this, 'onOpen']);
        $this->ws->on('message',  [$this, 'onMessage']);
        $this->ws->on('close',  [$this, 'onClose']);

        $this->ws->start();
    }

    public function onOpen($server, $request)
    {
        //$this->redis->hSet('websocket',$request->fd, '');
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage($server, $frame)
    {
        $data_arr = json_decode($frame->data, true);
        if(is_null($data_arr)) {
            $server->disconnect($frame->fd, 1000, '信息格式错误'); //主动关闭连接
        }

        if(!isset($data_arr['type'])) {
            $server->disconnect($frame->fd, 1000, '缺少字段信息'); //主动关闭连接
        }else {
            switch ($data_arr['type']) {
                case "login" :
                    $this->redis->hSet('user_id_fd', $frame->fd, $data_arr['user_id']);
                    break;
                default :
                    $server->push($frame->fd, 'send null');
            }
        }
    }

    public function onClose($ser, $fd)
    {
        $this->redis->hDel('websocket', $fd);
        echo "client {$fd} closed\n";
    }
}