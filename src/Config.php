<?php
/**
 * Created by PhpStorm.
 * User: zhaoziping
 * Date: 2019-03-15
 * Time: 10:31
 */

namespace Simple\Cache;


class Config
{
    ####缓存驱动配置####
    const DRIVER="redis";
    ####文件缓存驱动相关配置####
    //缓存文件存放路径
    const FILE_PATH="./tmp/cache/";
    ####Redis缓存驱动相关配置###
    const REDIS_HOST="tcp://laradock_redis_1:6379";
    ####Redis密码不存在可以不设置############
    const REDIS_PASSWORD="";
    //redis缓存的前缀
    const REDIS_PREFIX="zhaoziping_";
}