<?php
namespace Whilegit\Tree\NormalGongpai;

use Whilegit\Database\Model;

class GongpaiModel extends Model{
    
    //以下四个变量，必须要在子类的定义中抄一遍，即使不赋值。原因是Model应用静态变量的迟绑定技术(参考self和static关键字的区别)
    protected static $table = 'while_gongpai'; // 数据表名称，子类若不告知，默认为模型名称的小写化
    protected static $pk = "id";      // 主键名称,子类若不告知，查表的主键
    protected static $fields = null;  // 表的字段，子类若不告知，查表
    protected static $redirect_map = array('user_id'=>'\Whilegit\Model\Virtual\While_Member');
    
    protected $grandParentsAry;   //对应 $data['grandParents']

    protected $parent;
    protected $children = array('left'=>null, 'right'=>null);
    /**
     * 构造函数
     * @param array $data
     */
    public function __construct($data = array()){
        parent::__construct($data);
        $this->parseGrandParentIds();
    }

    /**
     * 当调用$this->setAttr('grandParents', xxxx)时自动回调本函数
     * @param string|array $value 如1:11;2:31;或array('1'=>11, '2'=>31)    
     */
    protected function setGrandParents($value){
        if(is_array($value)){
            $str = '';
            foreach($value as $k=>$v){
                $str .= "{$k}:{$v},";
            }
            $this->grandParentsAry = $value;
            $this->data['grandParents'] = rtrim($str,',');
        } else {
            $this->data['grandParents'] = $value;
            $this->parseGrandParentIds();
        }
    }
    
    /**
     * 将形如 1:11;2:31; 的字符串转化成 array('1'=>11,'2'=>31) 类型的数组
     */
    private function parseGrandParentIds(){
        if(!empty($this->data['grandParents'])){
            $this->grandParentsAry = array();
            $tmp1 = explode(',', $this->data['grandParents']);
            foreach($tmp1 as $item){
                $tmp2 = explode(':', $item);
                if(count($tmp2) == 2){
                    $level = trim($tmp2[0]);
                    $this->grandParentsAry[$level] = trim($tmp2[1]);
                }
            }
        }
    }
    
    public function addSub($user_id){
        $tmp = self::get(array('user_id' => $user_id));
        if(!empty($tmp)){
            die('ERROR: Already exists! user_id=' . $user_id);
        }
        $entry = $this->findPosition();
        if(empty($entry)){
            die('ERROR: 不能找到插入点! user_id=' . $user_id . ', tid= ' . $this->data['user_id']);
        }
        $child = new self();
        $child->setAttr('user_id', $user_id);
        $child->setAttr('level', $entry['level'] + 1);
        $grandParents = $entry['level'] . ':' . $entry['user_id'] . ',' . $entry['grandParents'];
        $child->setAttr('grandParents', rtrim($grandParents, ','));
        $child->setAttr('parentUserId', $entry['user_id']);
        $leftOrLeft = empty($entry->children['left']) ? 0 : 1;
        $child->setAttr('floor_sn', $entry['floor_sn'] * 2 + $leftOrLeft);
        $child->save();
    }
    
    public function findPosition(){
        $ret = null;
        
        $tmp = "{$this->data['level']}:{$this->data['user_id']}";
        $children = self::ls(array('grandParents FIND_IN_SET'=>$tmp), null, 'user_id');
        
        //本级不全，直接返回
        if(empty($children)){
            return $this;
        } else if(count($children) == 1){
            $this->children['left'] = array_pop($children);
            return $this;
        }
        
        $countInfo = array($this->data['level'] => 1);
        $floorFirstItems = array($this->data['level'] => $this);

        foreach($children as $child){
            $pid = $child['parentUserId'];
            $cid = $child['user_id'];
            $level = $child['level'];
            if(empty($countInfo[$level])) $countInfo[$level] = 1;
            else $countInfo[$level] ++;
            if(empty($floorFirstItems[$level])) $floorFirstItems[$level] = $child;
            
            if($pid == $this->data['user_id']){
                $child['parent'] = $this;
                if(empty($this->children['left'])){
                    $this->children['left'] = $child;
                } else {
                    $this->children['right'] = $child;
                }
            } else {
                $child['parent'] = $children[$pid];
                $parent = $children[$pid];
                if(empty($parent->children['left'])){
                    $parent->children['left'] = $child;
                } else {
                    $parent->children['right'] = $child;
                }
            }
        }
        ksort($countInfo);
        $preCount = null;
        $destLevel = null;
        foreach($countInfo as $k=>$v){
            if($preCount === null) {
                $preCount = $v;
                continue;
            }
            if($v != $preCount * 2){
                $destLevel = $k;
                break;
            } else {
                $preCount = $v;
            }
        }
        
        if($destLevel === null){
            //刚好全排列，那个取最后一排的第一个位置作为新插入的父级
            ksort($floorFirstItems);
            $ret = array_pop($floorFirstItems);
        } else {
            //中间有空位
            $ret = $this->findPositionRecur($destLevel - 1);
        }
        return $ret;
    }
    
    private function findPositionRecur($destLevel){
        $ret = null;
        
        $left = $this->children['left'];
        $right = $this->children['right'];
        $level = $this->data['level'];
        
        if($level > $destLevel){
            $ret = null;
        } else if($level == $destLevel) {
            $ret = (empty($left) || empty($right)) ? $this : null;
        } else {
            $ret = $left->findPositionRecur($destLevel);
            if($ret == null) {
                $ret = $right->findPositionRecur($destLevel);
            }
        }
        return $ret;
    }
    
    /*
     * 字段：
     *       id : 编号 
     *  user_id : 会员ID
     *    level : 公排等级
     *  floor_sn: 层级排序(绝对定位)
     * parentUserId : 父级user_id
     * grandParents: 所有上级的组成的字符串，如 4:xxxx;3:xxxx;2:xxxx;1:xxxx;  适合sql查询
     *
     */
}
