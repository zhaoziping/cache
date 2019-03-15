<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-13
 * Time: 21:42
 */

namespace Simple\Cache;


use Simple\Cache\Exception\MethodNotFoundException;

class CacheManager implements CacheInterface
{
    protected $driver;
    public function __construct()
    {
        $this->store();
    }

    public function store()
    {
        $method="create".ucfirst(Config::DRIVER)."Driver";
        if (method_exists($this,$method)){
            $this->$method();
        }else{
            throw new MethodNotFoundException("没有找到这个驱动的方法{$method}");
        }
    }
    public function createRedisDriver()
    {
        $this->driver=new RedisStore();
    }
    public function createFileDriver()
    {
        $this->driver=new FileStore();
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
        return $this->driver->set($key,$value,$ttl);
    }

    /**
     * 获取缓存如果不存在返回null
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->driver->get($key);
    }

    /**
     * 删除一个缓存
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * 清除所有的缓存
     * @return mixed
     */
    public function clear()
    {
        return $this->driver->clear();
    }

    /**
     * 获取多个key的值
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->driver->getMultiple($keys,$default);
    }

    /**
     * 设置多个value
     * @param $values
     * @param null $ttl
     * @return mixed
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->driver->setMultiple($values,$ttl);
    }

    /**
     * 删除多个key
     * @param $keys
     * @return mixed
     */
    public function deleteMultiple($keys)
    {
       return $this->driver->deleteMultiple($keys);
    }

    /**
     * 判断一个key是否存在
     * @return mixed
     */
    public function has($key)
    {
       return $this->driver->has($key);
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
        return $this->driver->remeber($key,$set,$ttl);
    }
}