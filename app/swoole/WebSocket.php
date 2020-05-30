<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/28
 * Time: 17:15
 */

namespace app\swoole;



class WebSocket
{
    private $ws;

    public function __construct()
    {
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
        $a = fopen('open.log','w+');
        fwrite($a, json_encode($server));
        fwrite($a, "\r\n\r\n\r\n\r\n");
        fwrite($a, json_encode($request));
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage($server, $frame)
    {
        $a = fopen('message.log','w+');
        fwrite($a, json_encode($server));
        fwrite($a, "\r\n\r\n\r\n\r\n");
        fwrite($a, json_encode($frame));
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is server");
    }

    public function onClose($ser, $fd)
    {
        $a = fopen('close.log','w+');
        fwrite($a, json_encode($ser));
        fwrite($a, "\r\n\r\n\r\n\r\n");
        fwrite($a, json_encode($fd));
        echo "client {$fd} closed\n";
    }
}