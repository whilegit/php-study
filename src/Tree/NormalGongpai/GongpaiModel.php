<?php
namespace Whilegit\Tree\NormalGongpai;

use Whilegit\Database\Model;

class GongpaiModel extends Model{
    
    //以下四个变量，必须要在子类的定义中抄一遍，即使不赋值。原因是Model应用静态变量的迟绑定技术(参考self和static关键字的区别)
    protected static $table = 'while_gongpai'; // 数据表名称，子类若不告知，默认为模型名称的小写化
    protected static $pk = "id";      // 主键名称,子类若不告知，查表的主键
    protected static $fields = null;  // 表的字段，子类若不告知，查表
    protected static $redirect_map = array('user_id'=>'\Whilegit\Model\Virtual\While_Member');
    
    // 打散后关联数组，对应 $this->data['grandParents']      5:8,4:6,3:3,2:2,1:1
    protected $grandParentsAry;

    protected $buildChildren = false;
    protected $children = array('left'=>null, 'right'=>null);
    protected $childrenLevelCountInfo = array();
    protected $childrenLevelFirstItemInfo = array();
    
    protected $buildParents = false;
    protected $parent;
    
    /**
     * 构造函数
     * @param array $data
     */
    public function __construct($data = array()){
        parent::__construct($data);
        $this->parseGrandParentIds();
    }

    /**
     * 在自己的树中，加入一个结点，并选择合适的位置插入(从上到下，从左到右)
     * @param int $user_id
     * @return \Whilegit\Tree\NormalGongpai\GongpaiModel
     */
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
        $child->setAttr('parentUserId', $entry['user_id']);
        //设置上级序列 
        $grandParents = $entry['level'] . ':' . $entry['user_id'] . ',' . $entry['grandParents'];
        $child->setAttr('grandParents', rtrim($grandParents, ','));
        //计算 floor_sn 的顺序
        $leftOrRight = empty($entry->children['left']) ? 0 : 1;
        $child->setAttr('floor_sn', $entry['floor_sn'] * 2 + $leftOrRight);
        $child->save();
        
        //设置子结点和父结点的相互关系
        $child->parent = $entry;
        $leftOrRight = ($leftOrRight == 0) ? 'left' : 'right';
        $entry->children[$leftOrRight] = $child;
        return $child;
    }
    
    /**
     * 寻找当前公排树(自己作为树根)的空缺位置
     * @desc <br> 依赖于子结点树的建立 见 function buildChildrenTree()
     * @return \Whilegit\Tree\NormalGongpai\GongpaiModel
     */
    private function findPosition(){
        $ret = null;
        if($this->buildChildren == false) $this->buildChildrenTree();
        
        if(empty($this->children['left']) || empty($this->children['right'])){
            return $this;
        }
        
        $destLevel = null;
        $tmp = null;
        foreach($this->childrenLevelCountInfo as $k=>$v){
            if($tmp === null) {
                $tmp = $v;
                continue;
            }
            //子树的各层级的数量，都应有 2^(相对level) 个数量，如不满足则此层必有空缺
            if($v != $tmp * 2){
                $destLevel = $k-1;
                break;
            } else {
                $tmp = $v;
            }
        }
        
        if($destLevel === null){
            //刚好全排列，取最后一排的第一个位置作为新插入的父级
            $ret = end($this->childrenLevelFirstItemInfo);
            reset($this->childrenLevelFirstItemInfo);
        } else {
            //中间有空位
            $ret = $this->findPositionRecur($destLevel);
        }
        return $ret;
    }
    
    /**
     * 建立当前对象的整个子树
     * @desc <br>对每个对象设置 $object->parent 和 $object->children['left'或'right']，使整颗子树相互关联
     * @desc <br>同时设置 $this->childrenLevelCountInfo (存储每一个子层级的结点数量)
     * @desc <br>同时设置 $this->childrenLevelFirstItemInfo (存储每一个子层级的第一个结点)
     */
    public function buildChildrenTree(){
        $tmp = "{$this->data['level']}:{$this->data['user_id']}";
        //按 level和floor_sn排列
        $children = self::ls(array('grandParents FIND_IN_SET'=>$tmp), null, 'user_id', array('level','floor_sn'));
        //本级不全，直接返回
        if(empty($children)){
            return;
        } else if(count($children) == 1){
            $child = array_pop($children);
            $child->parent = $this;
            $this->children['left'] = $child;
            $this->childrenLevelCountInfo = array($child['level'] => 1);
            $this->childrenLevelFirstItemInfo = array($child['level'] => $child);
            return;
        } else {
            foreach($children as $child){
                $pid = $child['parentUserId'];
                $cid = $child['user_id'];
                $level = $child['level'];
                
                //增加每层的结点数量
                if(empty($this->childrenLevelCountInfo[$level])) $this->childrenLevelCountInfo[$level] = 1;
                else $this->childrenLevelCountInfo[$level] ++;
                //获取每层的第一个结点
                if(empty($this->childrenLevelFirstItemInfo[$level])) $this->childrenLevelFirstItemInfo[$level] = $child;
                
                $parent = ($pid == $this->data['user_id']) ? $this : $children[$pid];
                if(empty($parent)){
                    die("[{$cid}]的父级为[{$pid}]，但是[$pid]不存在于树中。插入点为[{$this->data['user_id']}]");
                }
                $child['parent'] = $parent;
                $parent_child_position = ($child['floor_sn'] % 2 == 0) ? 'left' : 'right';
                $parent->children[$parent_child_position] = $child;
            }
            ksort($this->childrenLevelCountInfo);
            ksort($this->childrenLevelFirstItemInfo);
        }
        $this->buildChildren = true;
    }
    
    /**
     * 递归插找空缺位置
     * @param int $destLevel 目录层级
     * @return NULL|\Whilegit\Tree\NormalGongpai\GongpaiModel
     */
    private function findPositionRecur($destLevel){
        $ret = null;
        
        $left = $this->children['left'];
        $right = $this->children['right'];
        $level = $this->data['level'];
        
        if($level > $destLevel){
            //当前对象的层级较深，肯定不是目标空缺
            $ret = null;
        } else if($level == $destLevel) {
            //当前对象的层级相同，如果左子结点或右子结点为空，则此结子即为可以新增子结点的结点
            $ret = (empty($left) || empty($right)) ? $this : null;
        } else {
            //当前子结点层级较高，直接继续递归查找
            $ret = $left->findPositionRecur($destLevel);
            if($ret == null) {
                $ret = $right->findPositionRecur($destLevel);
            }
        }
        return $ret;
    }
    
    
    
    /**
     * 查找本对象的所有上级，并建立父树
     * @desc 设置自己的 $this->parent, 自己的直接上级 $this->parent->children['left'或'right']，一直到 根结点
     */
    public function buildParentTree(){
        $this->buildParents = true;
        //自己是根结点，直接退出
        if(empty($this->grandParentsAry)){
            return; 
        }
        
        //获取所有上级
        $parents = self::ls(array('user_id' => $this->grandParentsAry), null, 'user_id');
        
        //自己和某个直接上级挂购
        $meid = $this->data['user_id'];
        $me_pid = $this->data['parentUserId'];
        $me_child_position = ($this->data['floor_sn'] % 2 == 0) ? 'left' : 'right';
        $this->parent = $parents[$me_pid];
        $this->parent->children[$me_child_position] = $this;
        
        //各上级相互挂购（子结点存有父结点的信息，父结点不存有子结点的信息）
        foreach($parents as $p){
            $ppid = $p['parentUserId'];
            $plevel = $p['level'];
            
            if($ppid == '-1' || $plevel == 1){
                //自己是根结点，不处理
                continue;
            } else {
                $pfloor_sn = $p['floor_sn'];
                $p->parent = $parents[$ppid];
                $child_position = ($pfloor_sn % 2 == 0) ? 'left' : 'right';
                $p->parent->children[$child_position] = $p;
            }
        }
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
    
    /**
     * 输出公排图
     * @param GongpaiModel|int|null $entry object|user_id|null  如输入null则查找根结点
     * @return string
     */
    public static function plot($entry = null){
        if(!$entry instanceof GongpaiModel){
            if(empty($entry)) $entry = self::get(array('level'=>1, 'parentUserId'=>-1));
            else $entry = self::get(array('user_id'=>intval($entry)));
        }
        
        if($entry->buildChildren == false) $entry->buildChildrenTree();
        $str = "【{$entry['user_id']}】\r\n";
        $str .= "<div>".$entry->plot_cur(1) . "</div>";
        return $str;
    }
    
    /**
     * 输出公排图的递归
     * @param int $viewLevel 递归的层级
     * @return string
     */
    private function plot_cur($viewLevel){
        $str = '';
        $pad_str0 = str_pad('', 12 * ($viewLevel-1), '|           ', STR_PAD_LEFT);
        $pad_str0 = str_replace(' ', '&nbsp;', $pad_str0);
        if(!empty($this->children['left'])){
            $user_id = $this->children['left']['user_id'];
            
            $str .= "<div class='item'>
                        <div class='key'>
                            <span>{$pad_str0}o-【{$user_id}】</span>
                        </div>
                        <div class='sub'>".
                        $this->children['left']->plot_cur($viewLevel + 1).
                        "</div>
                    </div>";
        }
        if(!empty($this->children['right'])){
            $user_id = $this->children['right']['user_id'];
            $str .= "<div class='item'>
                        <div class='key'>
                            <span>{$pad_str0}o-【{$user_id}】</span>
                        </div>
                        <div class='sub'>".
                        $this->children['right']->plot_cur($viewLevel + 1).
                        "</div>
                    </div>";
        }
        return $str;
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
