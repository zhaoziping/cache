<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-15
 * Time: 15:17
 */

namespace Simple\Cache;


use Predis\Client;

class RedisStore implements CacheInterface
{
    protected $redis;
    protected $prefix;
    public function __construct()
    {
        $this->redis=new Client(Config::REDIS_HOST);
        if (Config::REDIS_PASSWORD){
            $this->redis->auth(Config::REDIS_PASSWORD);
        }
        $this->prefix=sha1(Config::REDIS_PREFIX?:"fastCache")."_";
    }

    /**
     * 设置缓存的key和value以及缓存的有效期
     * @param $key
     * @param $value
     * @param null $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->redis->setex($this->prefix.$key,$ttl,$value)->getPayload()=="OK"?
            $this->get($key):false;
    }

    /**
     * 获取缓存如果不存在返回null
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->redis->get($this->prefix.$key);
    }

    /**
     * 删除一个缓存
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->redis->del([$this->prefix.$key]);
    }

    /**
     * 清除所有的缓存返回删除的缓存个数
     * @return mixed
     */
    public function clear()
    {
        return $this->redis->del($this->redis->keys("*"));
    }

    /**
     * 获取多个key的值
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public function getMultiple($keys, $default = null)
    {
        return array_combine($keys,array_map(function ($v){
            return $this->get($v);
        },$keys));
    }

    /**
     * 设置多个value
     * @param $values
     * @param null $ttl
     * @return mixed
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $k=>$v){
            $this->set($k,$v,$ttl);
        }
        return true;
    }

    /**
     * 删除多个key
     * @param $keys
     * @return mixed
     */
    public function deleteMultiple($keys)
    {
        return array_combine($keys,array_map(function ($v){
            return $this->delete($v);
        },$keys));
    }

    /**
     * 判断一个key是否存在
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        return $this->redis->exists($this->prefix.$key);
    }

    /**
     * 判断一个key是否存在如果不存在创建
     * @param $key
     * @param \Closure $set
     * @param null $ttl
     * @return mixed
     */
    public function remember($key, \Closure $set, $ttl = null)
    {
        return $this->has($key)?$this->get($key):$this->set($key,$set(),$ttl);
    }
}