<?php
namespace Whilegit\Tree\Gongpai;
use Whilegit\Utils\Trace;

class EcshopMiddle implements MiddleInterface{
    private $ecs;
    private $db;
    private $field;
    
    public function __construct($ecs, $db, $field = 'parent_id'){
        $this->ecs = $ecs;
        $this->db = $db;
        $this->field = $field;
    }
    
    public function getChildren($uid){
        $sql = "select user_id from " . $this->ecs->table('users') . " where " . $this->field . "={$uid}";
        $result = $this->db->getAll($sql);
        if(!empty($result)){
            $tmp = array();
            foreach($result as $r){
                $tmp[] = $r['user_id'];
            }
            $result = $tmp;
        }
        return $result;
    }
    
    public function getParent($uid){
        $sql = "select ". $this->field ." from " . $this->ecs->table('users') . " where user_id={$uid}";
        $result = $this->db->getOne($sql);
        if($result === '') $result = false;
        return $result;
    }
    
    public function getRoots(){
        $sql = "select user_id from " . $this->ecs->table('users') . " where " . $this->field . "=-1";
        $result = $this->db->getAll($sql);
        if(!empty($result)){
            $tmp = array();
            foreach($result as $r){
                $tmp[] = $r['user_id'];
            }
            $result = $tmp;
        }
        return $result;
    }
    
    public function isRoot($uid){
        $parentId = $this->getParent($uid);
        return $parentId == -1;
    }
    
    public function setParent($uid, $parentId){
        return $this->db->autoExecute($this->ecs->table('users'), array($this->field=>$parentId), 'UPDATE', "user_id = '$uid'");
    }
    
    public function setRoot($uid){
        return $this->db->autoExecute($this->ecs->table('users'), array($this->field=>-1), 'UPDATE', "user_id = '$uid'");
    }
    
    public function hasParent($uid){
        $sql = "select " . $this->field . " from " . $this->ecs->table('users') . " where user_id={$uid}";
        $parentId = $this->db->getOne($sql);
        if($parentId === false) return false;
        else if($parentId == -1) return true;   //根结点
        else if($parentId == 0) return false;
        else return true;
    }
    
    public function log($str){
        Trace::log($str);
        //file_put_contents(dirname(__DIR__) . '/lib_gongpai.log', '['.date('Y-m-d H:i:s') . ']:' . $str . "\r\n", FILE_APPEND | LOCK_EX);
    }
}