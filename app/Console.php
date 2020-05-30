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
        switch ($type) {
            case "websocket" :
                new WebSocket();
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