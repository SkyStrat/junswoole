<?php
declare(strict_types = 1);

/**
 * 助手函数
 */

if(!function_exists('config')) {
    function config() {
        return [
            'websocket' => [
                'ip' => '0.0.0.0',
                'port' => 9501,
            ],
            'set' => [
                'worker_num' => 8,
                'reload_async' => true,
                'max_wait_time' => 3
            ],
            'redisset' => [
                'ip' => '192.168.27.3',
                'port' => 6379
            ],
        ];
    }
}