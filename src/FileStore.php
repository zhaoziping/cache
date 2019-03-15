<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-13
 * Time: 21:17
 */

namespace Simple\Cache;


class FileStore implements CacheInterface
{
    use Filesystem;
    protected $directory;
    public function __construct()
    {
        $this->directory=Config::FILE_PATH;
    }

    /**
     * 如果文件的路径不存在的话创建路径
     * @param $path
     */
    protected function ensureCacheDirectoryExists($path)
    {
        if (!$this->exists(dirname($path))){
            $this->makeDirectory(dirname($path),0777,true,true);
        }
    }

    /**
     * 根据key生成一个缓存存放的路径
     * @param $key
     * @return string
     */
    protected function path($key){
        $parts=array_slice(str_split($hash=sha1($key),2),0,2);
        return $this->directory."/".implode("/",$parts)."/".$hash;
    }

    /**
     * 获取数据
     * @param $key
     * @return array
     */
    public function getPayload($key){
        $path=$this->path($key);
        try{
            $expire=substr($contents=$this->pull($path,true),0,10);
        }catch (\Exception $exception){
            return $this->emptyPayload();
        }
        if (time()>=$expire){
            $this->delete($key);
            return $this->emptyPayload();
        }
        $data=unserialize(substr($contents,10));
        $time=time()-$expire;
        return compact('data','time');
    }

    /**
     * 不存在的话返回空的数据
     * @return array
     */
    protected function emptyPayload()
    {
        return ['data' => null, 'time' => null];
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
        $this->ensureCacheDirectoryExists($path=$this->path($key));
        $this->put($path,time()+$ttl.serialize($value),true);
        return $this->get($key);
    }

    /**
     * 获取缓存的数据
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->getPayload($key)['data']?:null;
    }

    /**
     * 删除一个缓存
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        if ($this->exists($file=$this->path($key))){
            return $this->forget($file);
        }
        return false;
    }

    /**
     * 清除所有的缓存
     * @return mixed
     */
    public function clear()
    {
        if (!$this->isDirectory($this->directory)){
            return false;
        }
        $this->delDirectory($this->directory);
        return true;
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
        return array_map(function ($v){
            return $this->delete($v);
        },$keys);
    }

    /**
     * 判断一个key是否存在
     * @param $key
     * @return bool|mixed
     */
    public function has($key)
    {
        return $this->get($key)?true:false;
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