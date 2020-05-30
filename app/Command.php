<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/28
 * Time: 13:56
 */

namespace app;


class Command
{
    private $welcome = <<<eof
Welcome To junswoole Command Console！！！
          .----.
       _.'__    `.
   .--($)($$)---/#\
 .' @          /###\
 :         ,   #####
  `-..__.-' _.-\###/
        `;_:    `"'
      .'"""""`.
     /,  ya ,\\
    // hello! \\
    `-._______.-'
    ___`. | .'___
   (______|______)

Current Register Command:
start:websocket

input your command:
eof;

    public function run()
    {
        fwrite(STDOUT, $this->welcome);
        $input = trim(fgets(STDIN));

        $input_arr = explode(':', $input);
        $action = $input_arr[0];
        $type = isset($input_arr[1]) ? $input_arr[1] : 'websocket';

        $console = new Console();
        $console->$action($type);
    }
}