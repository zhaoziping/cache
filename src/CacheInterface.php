<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-13
 * Time: 21:04
 */
namespace Simple\Cache;
interface CacheInterface
{
    /**
     * 设置缓存的key和value以及缓存的有效期
     * @param $key
     * @param $value
     * @param null $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = null);

    /**
     * 获取缓存如果不存在返回null
     * @param $key
     * @return mixed
     */
    public function get($key);
    /**
     * 删除一个缓存
     * @param $key
     * @return mixed
     */
    public function delete($key);

    /**
     * 清除所有的缓存
     * @return mixed
     */
    public function clear();

    /**
     * 获取多个key的值
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public function getMultiple($keys,$default=null);

    /**
     * 设置多个value
     * @param $values
     * @param null $ttl
     * @return mixed
     */
    public function setMultiple($values,$ttl=null);

    /**
     * 删除多个key
     * @param $keys
     * @return mixed
     */
    public function deleteMultiple($keys);

    /**
     * 判断一个key是否存在
     * @return mixed
     */
    public function has($key);

    /**
     * 判断一个key是否存在如果不存在创建
     * @param $key
     * @param \Closure $set
     * @param null $ttl
     * @return mixed
     */
    public function remember($key,\Closure $set,$ttl=null);
}