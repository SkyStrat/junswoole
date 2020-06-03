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
    private $config;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;

        $this->config = config();
        $this->ws = new \swoole_websocket_server($this->config['websocket']['ip'], $this->config['websocket']['port']);
        $this->ws->set($this->config['set']);
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
                    $swoole_mysql = new \Swoole\Coroutine\MySQL();
                    $swoole_mysql->connect($this->config['mysqlset']);
                    $sql = "select a.isread, b.title, b.content from message_accept a left join backround_message b on a.msg_id = b.id where a.accept_account = ? and b.status = ?";
                    $stmt = $swoole_mysql->prepare($sql);
                    if ($stmt) {
                        $result = $stmt->execute(array($data_arr['user_id'], 1));
                        $server->push($frame->fd, json_encode($result));
                    } else {
                        echo $swoole_mysql->errno.$swoole_mysql->error."\n";
                        $error = fopen('mysql.log', 'a');
                        fwrite($error, date('Y-m-d H:i:s')." [mysql-error-message] errno：".$swoole_mysql->errno."\r\n error：".$swoole_mysql->error."\r\n\r\n");
                        fclose($error);
                    }

                    break;
                default :
                    $server->push($frame->fd, 'send null');
            }
        }
    }

    public function onClose($ser, $fd)
    {
        $this->redis->hDel('user_id_fd', $fd);
        echo "client {$fd} closed\n";
    }
}