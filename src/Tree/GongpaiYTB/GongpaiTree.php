<?php 
namespace Whilegit\Tree\GongpaiYTB;

class GongpaiTree{
    
    /**
     * 公排类型，二二公排时 $type为2， 三三公排时 $type为3
     * @desc <pre>
     *           用于找出新用户在公排中的位置(positionUser和findPosition函数)
     *           用于检验公排树的完整性(validate函数)，
     *           不用于output打印函数不涉及$type，任何树的类型都可以打印
     *       </pre>
     */
    private $type;
    
    //公排中间件，负责持久层，继承自 TreeMiddleInterface 接口
    private $middle;
    
    /**
     * 构造函数
     * @param MiddleInterface $middle 持久中间层
     * @param number $type  公排类型，二二公排填2
     */
    public function __construct(MiddleInterface $middle, $type = 2){
        if(empty($middle)) die('gpm参数不能为空');
        if($type < 2) die('type参数不能小于2');
        $this->middle = $middle;
        $this->type = $type;
    }
    
    /**
     * 将用户加入公排树中，并返回插入位置的直接上级
     * @param int $uid
     * @param array $group 某一层级的所有用户的id，如为空则搜索所有根结点
     * @return int|false 返回整型，表示其直接上级，出错后返回false
     */
    public function positionUser($uid, $group = array()){
        if($this->middle->getParent($uid)){
            $this->middle->log("错误：用户[{$uid}]已经有上级，不能给他重新排位  - " . __LINE__);
            return false;
        }
        
        if(empty($group)){
            $group = $this->middle->getRoots();
            if(empty($group)){
                $this->middle->log("错误：没有指定根结点，又没有找到根结点，无法插入用户[{$uid}]  - " . __LINE__);
                return false;
            }
        }
        //插入位置的直接上级
        $parentId = $this->findPosition($group);
        
        if(empty($parentId)){
            $this->middle->log("错误：无法为用户[{$uid}]找到公排放置的位置，根点位为[{$root}]。 - " . __LINE__);
            return false;
        }
        
        $this->middle->setParent($uid, $parentId);
        return $parentId;
    }
    
    /**
     * 递归获取公排新插入的可用点位
     * @param array $group 整型数组，通常为某一层级的全部用户
     * @return int|false 整型时表示插入点位的父级id, 为false表示出错
     */
    private function findPosition(&$group){
        if(empty($group)) {
            $this->middle->log("警告：参数 group 为空 - " . __LINE__);
            return false;
        }
        
        $new_group = array();
        foreach($group as $gid){
            $children = $this->middle->getChildren($gid);
            if($children === false){
                $this->middle->log("错误：用户[{$gid}]结点可能不存在 - " . __LINE__);
                continue;
            }
            $num_children = count($children);
            if($num_children >= $this->type){
                if($num_children > $this->type){
                    $this->middle->log("警告：用户[{$gid}]的下级个数({$num_children})超为 {$this->type}个 - " . __LINE__);
                    array_splice($children, 2);
                }
                $new_group = array_merge($new_group, $children);
            } else {
                return $gid;
            }
        }
        
        if(empty($new_group)){
            $this->middle->log("错误：结点群都无法获取直接下级。group参数：". var_export($group, true)." - " . __LINE__);
            return false;
        }
        return $this->findPosition($new_group);
    }
    
    
    /**
     * 获取子孙各代
     * @param int $uid
     * @param string $level 查询的级别，1只到直接子代，2到孙代...
     */
    public function getGrandChildren($uid, $level = PHP_INT_MAX){
        $children = $this->middle->getChildren($uid);
        if(empty($children)) return array();
        $ary = array();
        foreach($children as $child){
            $tmp = array();
            if($level - 1 > 0){
                $tmp = $this->getGrandChildren($child, $level - 1);
            }
            $ary[$child] = $tmp;
        }
        return $ary;
    }
    
    /**
     * 获取祖先各级id
     * @param int $uid
     * @param int $level  查询的级别 ，1只到父级， 2到祖父级...
     * @return array 返回祖父各级的id
     */
    public function getGrandParents($uid, $level = PHP_INT_MAX){
        $parentIds = array();
        for($i = 0; $i < $level; $i++){
            $pid = $this->middle->getParent($uid);
            if($pid === false){
                $this->middle->log("警告：用户[{$uid}]无法获取上级uid");
                break;
            }
            $uid = $pid;
            $parentIds[] = $pid;
            if($this->middle->isRoot($pid)){
                break;
            }
        }
        return $parentIds;
    }
    
    /**
     * 编历用户树，查看结构的完整性
     * @param int $uid 从该用户开始查询树的完整性
     * @return boolean|int true完整, false表示不完整，用户树存在问题
     */
    public function validate($root = 0){
        $ret = true;
        $ids = array();
        if($root == 0){
            $rootsAry = $this->middle->getRoots();
            if(empty($rootsAry)){
                $this->middle->log("错误：没有找到根结点，无法判定完整性  - " . __LINE__);
                return false;
            }
            $ids = $rootsAry;
        } else {
            $ids = array($root);
        }
        
        $last_floor_flag = false;
        $last_floor_uid = null;
        while(true){
            $exit_flag = false;
            $tmpIds = array();
            foreach($ids as $uid){
                $children = $this->middle->getChildren($uid);
                if($children === false){
                    $exit_flag = true;
                    $ret = $uid;
                    $this->middle->log("错误：用户[{$uid}]无法获取子代  - " . __LINE__);
                    break;
                }
                $num = count($children);
                if($last_floor_flag){
                    if($num > 0){
                        $exit_flag = true;
                        $ret = $last_floor_uid;
                        $this->middle->log("错误：空洞，上一层用户[{$last_floor_uid}]子代没有填满，而下一层用户[{$uid}]却有{$num}个子代  - " . __LINE__);
                        break;
                    }
                    continue;
                }
                if($num > $this->type){
                    $exit_flag = true;
                    $ret = $uid;
                    $this->middle->log("错误：用户[{$uid}]的子代个数({$num})过多  - " . __LINE__);
                    break;
                } else {
                    if($num < $this->type){
                        if($last_floor_flag === false) {
                            $last_floor_flag = true;
                            $last_floor_uid = $uid;
                        }
                    }
                    if($num > 0){
                        $tmpIds = array_merge($tmpIds, $children);
                    }
                }
            }
            if($exit_flag == true){
                break;
            }
            if(!empty($tmpIds)){
                $ids = $tmpIds;
            } else {
                break;
            }
        }
        return $ret;
    }
    
    /**
     * 返回公排图
     * @return string
     */
    public function output(){
        $group = $this->middle->getRoots();
        $str = '';
        for($i = 0; $i<count($group); $i++){
            $str .= "<br><br><br>=============================================<br>{$group[$i]}";
            if($i != 0) $str .= "<br>";
            $str .= $this->output_cur($group[$i], 1, ($i+1 == count($group)));
        }
        return $str;
    }
    
    /**
     * 公排图递归
     * @param int $uid
     * @param int $level
     * @param boolean $last_flag
     * @return string
     */
    private function output_cur($uid, $level, $last_flag){
        $str = '';
        $children = $this->middle->getChildren($uid);
        if(empty($children)) return '';
        for($i = 0; $i<count($children); $i++){
            $str .= "\r\n";
            if($last_flag){
                $pad_str0 = str_pad('', 12 * ($level-1), '|           ', STR_PAD_LEFT);
            } else {
                $pad_str0 = str_pad('', 12 * ($level-1), '|           ', STR_PAD_LEFT);
            }
            
            //$str .= "$pad_str0";
            $pad_str1 = str_pad($children[$i], 11, "-", STR_PAD_LEFT);
            $str .= $pad_str0 . 'o' . $pad_str1;
            $str .= $this->output_cur($children[$i], $level+1, ($i+1 == count($children)));
        }
        return $str;
    }
}
