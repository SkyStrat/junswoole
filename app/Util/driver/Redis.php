<?php 

namespace app\Util\driver;

use app\Util\Driver;
 
class Redis extends Driver
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $func          = $this->options['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Redis;
        $this->handler->$func($this->options['host'], $this->options['port'], $this->options['timeout']);

        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
    }

    public function getHandler(){
        return self::handler();
    }

    public function flushAll()
    {
        return $this->handler->flushAll();
    }

    public function save(){
        return $this->handler()->save();
    }

    public function keys($keys = '*'){
        $list = $this->handler()->keys($this->options['prefix'].$keys);
        if(!empty($this->options['prefix'])){
            $length = mb_strlen($this->options['prefix']);
            foreach ($list as $k => $val){
                $list[$k] = mb_substr($val, $length);
            }
        }
        return $list;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->handler->get($this->getCacheKey($name)) ? true : false;
    }

    public function exists($name){
        return $this->handler->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = $this->handler->get($this->getCacheKey($name));
        if (is_null($value)) {
            return $default;
        }
        $jsonData = json_decode($value, true);
        // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据 byron sampson<xiaobo.sun@qq.com>
        return (null === $jsonData) ? $value : $jsonData;
    }

    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param integer   $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        //对数组/对象数据进行缓存处理，保证数据完整性  byron sampson<xiaobo.sun@qq.com>
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }
        
        /*------存redis的key名称，免得存了什么key都不知道-----*/
        /*$insert = [
            'redis_key' => $name,
            'time_out'  => empty($expire)? 0 : $expire
        ];
        Db::connect('db_admin')->query('REPLACE INTO redis_key (redis_key,time_out)VALUE("'.$insert['redis_key'].'","'.$insert['time_out'].'")');*/
        /*------存redis的key名称，免得存了什么key都不知道-----*/
        
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    public function hSet($name, $key, $value)
    {
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $name = $this->getCacheKey($name);
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $this->handler->hSet($name, $key, $value);
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    public function hGet($name, $key, $default = false)
    {
        $value = $this->handler->hGet($this->getCacheKey($name), $key);
        if (is_null($value)) {
            return $default;
        }
        $jsonData = json_decode($value, true);
        // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据 byron sampson<xiaobo.sun@qq.com>
        return (null === $jsonData) ? $value : $jsonData;
    }

    public function hGetAll($name)
    {
        $value = $this->handler->hGetAll($this->getCacheKey($name));

        foreach ($value as $k => $val){
            if($json_Data = json_decode($val, true)){
                $value[$k] = $json_Data;
            }
        }
        // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据 byron sampson<xiaobo.sun@qq.com>
        return $value;
    }

    public function hExists($name, $key)
    {
        return $this->handler->hExists($this->getCacheKey($name), $key);
    }

    public function hDel($name, $key)
    {
        return $this->handler->hDel($this->getCacheKey($name), $key);
    }



    public function rPush($name, $value, $expire = null){
        if(is_null($expire))
            $expire = $this->options['expire'];
        if($expire instanceof  \DateTime)
            $expire = $expire->getTimestamp() - time();
        if ($this->tag && ! $this->has($name)){
            $first = true;
        }
        $key = $this->getCacheKey($name);
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->rPushx($key, $value);
        } else {
            $result = $this->handler->rPush($key, $value);
        }
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    public function lPush($name, $value, $expire = null){
        if(is_null($expire))
            $expire = $this->options['expire'];
        if($expire instanceof  \DateTime)
            $expire = $expire->getTimestamp() - time();
        if ($this->tag && ! $this->has($name)){
            $first = true;
        }
        $key = $this->getCacheKey($name);
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->lPushx($key, $value);
        } else {
            $result = $this->handler->lPush($key, $value);
        }
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    public function lRange($name, $start, $end){
        $value = $this->handler->lRange($this->getCacheKey($name), $start, $end);
        foreach ($value as $k => $val){
            if($json_Data = json_decode($val, true)){
                $value[$k] = $json_Data;
            }
        }
        // 检测是否为JSON数据 true 返回JSON解析数组, false返回源数据 byron sampson<xiaobo.sun@qq.com>
        return $value;
    }

    public function lPop($name){
        $value = $this->handler->lPop($this->getCacheKey($name));
        $jsonData = json_decode($value, true);
        return (null === $jsonData) ? $value : $jsonData;
    }

    public function rPop($name){
        $value = $this->handler->rPop($this->getCacheKey($name));
        $jsonData = json_decode($value, true);
        return (null === $jsonData) ? $value : $jsonData;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        /*----------删掉缓存同时删掉redis_key表的名称----------*/
        $where['redis_key'] =  $name;
        Db::connect('db_admin')->table('redis_key')->where($where)->delete();
        /*----------删掉缓存同时删掉redis_key表的名称----------*/
        
        return $this->handler->delete($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->handler->delete($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return $this->handler->flushDB();
    }

}
