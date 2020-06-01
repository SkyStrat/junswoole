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
use app\Util\driver\Redis;

class Console implements ConsoleInterface
{
    public function start(String $type)
    {
        echo "start\n";
        //$redis = new \Redis();
        $config = config();
        switch ($type) {
            case "websocket" :
                $config['redisset']['select'] = 0; //标识选择0号库
                $redis = new Redis($config['redisset']);
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