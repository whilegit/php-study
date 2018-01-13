<?php
namespace Whilegit\Tree\Gongpai;

interface MiddleInterface{
    
    /**
     * 获取直接子代
     * @param int $uid
     * @return array|false 成功返回直接子代的数组(可能为空数组)，失败返回false(比如$uid不存在)
     */
    function getChildren($uid);
    
    /**
     * 获取直接父级id
     * @param int $uid
     * @return int|false 成功返回父代id, 失败返回false(比如uid不存在)
     */
    function getParent($uid);
    
    /**
     * 获取根结点id(可能有多个)
     * @return array() 整型数组(可能为空数组)，出错返回false
     */
    function getRoots();
    
    /**
     * 是否是根节点
     * @return true|false   true是根结点，false不是或出错(如$uid不存在等)
     */
    function isRoot($uid);
    
    /**
     * 判定是否有直接上级 (根结点也算有上级)
     * @param int $uid
     * @return false|true   true表示有正常上级或本身是根结点, false表示出错或没有上级
     */
    function hasParent($uid);
    
    /**
     * 设置用户的父级
     * @param int $uid
     * @param int $parentId
     * @return boolean true成功  false失败
     */
    function setParent($uid, $parentId);
    
    /**
     * 设为根结点
     */
    function setRoot($uid);
    
    /**
     * 日志记录
     */
    function log($str);
}
