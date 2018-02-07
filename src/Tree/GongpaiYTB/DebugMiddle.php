<?php
namespace Whilegit\Tree\GongpaiYTB;
use Whilegit\Utils\Trace;

 class DebugMiddle implements MiddleInterface{
     
    private $ids = array(999  => -1, 1000 => 999, 1001 => 999, 1002 => 1000,
                          1003 => 1000, 1004 => 1001, 1005 => 1001, 1006 => 1002,
                          1007 => 1002, 1008 => 1003, 1009 => 1003, 1010 => 1004,
                          1011 => 1004, 1012 => 1005, 1013 => 1005, 1014 => 1006,
                          1015 => 1006,1016 => 0,1017 => 0);
    
    public function getChildren($uid){
        if(!isset($this->ids[$uid])) return false;
        $ret = array();
        foreach($this->ids as $k=>$v){
            if($v == $uid){
                $ret[] = $k;
            }
        }
        return $ret;
    }
 
    public function getParent($uid){
        if(!isset($this->ids[$uid])) return false;
        return $this->ids[$uid];
    }
 
    public function getRoots(){
        $ret = array();
        foreach($this->ids as $k=>$v){
            if($this->isRoot($k)){
                $ret[] = $k;
            }
        }
        return $ret;
    }
    
    public function isRoot($uid){
        if(!isset($this->ids[$uid])) return false;
        return $this->ids[$uid] == -1;
    }
 
     public function hasParent($uid){
         if(!isset($this->ids[$uid])) return false;
         $parentId = $this->getParent($uid);
         if($parentId === false) return false;
         else if($parentId == -1) return true;   //根结点
         else if($parentId == 0) return false;
         else return true;
     }
 
     public function setParent($uid, $parentId){
         if(!isset($this->ids[$uid])) return false;
         $this->ids[$uid] = $parentId;
         return true;
     }
     
     public function setRoot($uid){
         if(!isset($this->ids[$uid])) return false;
         $this->ids[$uid] = -1;
         return true;
     }

     public function log($str){
         Trace::log($str);
         //file_put_contents(dirname(__DIR__) . '/lib_gongpai.log', '['.date('Y-m-d H:i:s') . ']:' . $str . "\r\n", FILE_APPEND | LOCK_EX);
     }
 }
 