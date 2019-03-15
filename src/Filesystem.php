<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-14
 * Time: 21:58
 */

namespace Simple\Cache;


use Simple\Cache\Exception\FileNotFoundException;

trait Filesystem
{
    /**
     * 判断一个文件是否存在
     * @param $path
     * @return bool
     */
    public function exists($path){
        return file_exists($path);
    }

    /**
     * 判断一个是否是一个文件
     * @param $file
     * @return bool
     */
    public function isFile($file){
        return is_file($file);
    }

    /**
     * 获取一个文件的大小
     * @param $path
     * @return int
     */
    public function size($path){
        return filesize($path);
    }

    /**
     * 获取文件的内容
     * @param $path
     * @param bool $lock
     * @return bool|false|string
     * @throws FileNotFoundException
     */
    public function pull($path,$lock=false){
        if ($this->isFile($path)){
            return $lock?$this->sharedGet($path):file_get_contents($path);
        }
        throw new FileNotFoundException("文件不存在:{$path}");
    }

    /**
     * 获取一个文件的内容的时候同时加锁
     * @param $path
     * @return bool|string
     */
    public function sharedGet($path){
        $contents='';
        $handle=fopen($path,'rb');
        if ($handle){
            try{
                if (flock($handle,LOCK_SH)){
                    clearstatcache(true,$path);
                    $contents=fread($handle,$this->size($path)?:1);
                    flock($handle,LOCK_UN);
                }
            }finally{
                fclose($handle);
            }
        }
        return $contents;
    }

    /**
     * 生成文件的hash
     * @param $path
     * @return string
     */
    public function hash($path){
        return md5_file($path);
    }

    /**
     * 写入内容到文件
     * @param $path
     * @param $contents
     * @param bool $lock
     * @return bool|int
     */
    public function put($path,$contents,$lock=false)
    {
        return file_put_contents($path,$contents,$lock?LOCK_EX:0);
    }

    /**
     * 往前追加文件内容
     * @param $path
     * @param $data
     * @return bool|int
     * @throws FileNotFoundException
     */
    public function prepend($path,$data){
        if ($this->exists($path)){
            return $this->put($path,$data.$this->get($path));
        }
        return $this->put($path,$data);
    }

    /**
     * 追加文件内容
     * @param $path
     * @param $data
     * @return bool|int
     */
    public function append($path,$data){
        return file_put_contents($path,$data,FILE_APPEND);
    }

    /**
     * 获取或者更改文件的权限
     * @param $path
     * @param null $mode
     * @return bool|string
     */
    public function chmod($path,$mode=null){
        if ($mode){
            return chmod($path,$mode);
        }
        return substr(sprintf('%o',fileperms($path)),-4);
    }

    /**
     * 删除文件
     * @param $paths
     * @return bool
     */
    public function forget($paths){
        $paths=is_array($paths)?$paths:func_get_args();
        $success=true;
        foreach ($paths as $path){
            try{
                if (!@unlink($path)){
                    $success=false;
                }
            }catch (\ErrorException $exception){
                $success=false;
            }
        }
        return $success;
    }

    /**
     * 复制文件
     * @param $path
     * @param $target
     * @return bool
     */
    public function move($path,$target){
        return rename($path,$target);
    }

    /**
     * 获取文件名称
     * @param $path
     * @return mixed
     */
    public function name($path){
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * 获取文件名
     * @param $path
     * @return mixed
     */
    public function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * 获取文件路径
     * @param $path
     * @return mixed
     */
    public function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 获取文件扩展名
     * @param $path
     * @return mixed
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 获取文件类型
     * @param $path
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }


    /**
     * 获取文件的最后修改时间
     * @param $path
     * @return bool|int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * 判断是否是一个目录
     * @param $directory
     * @return bool
     */
    public function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * 判断文件是否可读
     * @param $path
     * @return bool
     */
    public function isReadable($path)
    {
        return is_readable($path);
    }

    /**
     * 判断文件是否可写
     * @param $path
     * @return bool
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * 创建一个目录
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * 删除一个目录
     * @param $path
     */
    public function delDirectory($path)
    {
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        $this->delDirectory($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }
}