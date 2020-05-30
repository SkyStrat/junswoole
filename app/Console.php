<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/28
 * Time: 14:33
 */

namespace app;

use app\consoleface\ConsoleInterface;
use app\swoole\WebSocket;

class Console implements ConsoleInterface
{
    public function start(String $type)
    {
        echo "start\n";
        $redis = new \Redis();
        switch ($type) {
            case "websocket" :
                $redis->select(0); //选择0号库
                new WebSocket($redis);
                break;
            default :
                echo "null start\n";
        }
    }

    public function __call($name, $arguments)
    {
        echo "null ".$name."\n";
    }

}