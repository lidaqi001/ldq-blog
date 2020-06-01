# 算法

> ## **排序算法**

> 冒泡排序
```
function bubble(&$nums)
{
    if (!count($nums))
        return;

    for ($i = 0; $i < count($nums); $i++) {
        $flag = true;
        // 每一次循环，都会把最大的值放到最后，所以循环条件每次递减$i-1
        // -1是因为每次都是对比 j>j+1，所以应该-1
        for ($j = 0; $j < count($nums) - $i - 1; $j++) {
            if ($nums[$j] > $nums[$j + 1]) {
                $temp = $nums[$j + 1];
                $nums[$j + 1] = $nums[$j];
                $nums[$j] = $temp;
                $flag = false;
            }
        }
        // 这里有一个优化，如果第一次循环，没有发生交换，就认为该数据已经有序
        if ($flag) {
            break;
        }
    }
}

$nums = [4, 5, 6, 3, 2, 1];
bubble($nums);
print_r($nums);
```

> 插入排序
```

```

> 选择排序
```

```

> 快速排序
```
function quick(&$nums)
{
    qsort($nums, 0, count($nums) - 1);
}
function qsort(&$nums, $begin, $end)
{
    if ($begin > $end)
        return;

    $pivot = $nums[$end];
    $i = $j = $begin;

    // 原理：将比$pivot小的数丢到[$begin...$i-1]中，剩下的[$i..$j]区间都是比$pivot大的
    for (; $j < $end; $j++) {
        // $j 比 $pivot 大
        if ($nums[$j] < $pivot) {
            // 交换位置，$i的值始终 比 $j的值要小
            $temp = $nums[$i];
            $nums[$i] = $nums[$j];
            $nums[$j] = $temp;
            // $i偏移量+1
            $i++;
        }
    }

    // 交换 $i 和 $pivot 的值
    // 这样最终 $pivot 就位于数组的中间位置
    // 因为此时的$i所处的位置，[0...$i-1] 都比 $pivot 大，[$i+1...$j] 都比 $pivot 小
    $temp = $nums[$i];
    $nums[$i] = $pivot;
    $nums[$end] = $temp;

    // 递归该流程，最终使整个数组变成有序的
    qsort($nums, $begin, $i - 1);
    qsort($nums, $i + 1, $end);
}


$nums = [4, 5, 6, 3, 2, 1];
quick($nums);
print_r($nums);
```

> 归并排序

> ## **查找算法**

> 二分查找
```

function dichotomy($nums, $search)
{
    $begin_index = 0;
    $end_index = count($nums) - 1;

    while ($begin_index <= $end_index) {

        // 每次取中间的那个索引
        $mid_index = intval(($begin_index + $end_index) / 2);

        // 要找的值 比 中间位置 数值大
        if ($search > $nums[$mid_index]) {
            // 将起始偏移量 更改为 中间位置+1
            $begin_index = $mid_index + 1;

        }
        // 要找的值 比 中间位置 数值小
        elseif ($search < $nums[$mid_index]) {
            // 将末尾偏移量 更改为 中间位置-1
            $end_index = $mid_index - 1;

        } 
        // 等于，找到了
        elseif ($search == $nums[$mid_index]) {

            return $mid_index;

        }
    }

    // 最终，没找到，返回-1
    return -1;
}

$res = dichotomy([4, 8, 12, 13, 29, 33, 55], 55);
print_r($res);

```

> ## **二叉树**

> 二叉树遍历

```
// 二叉链表节点
class Node
{
    public $data;
    public $left = null;
    public $right = null;

    public function __construct($data)
    {
        $this->data = $data;
    }
}

/**
 * 前序遍历
 * @param Node $tree
 */
function preOrderTraverse($node)
{
    if (is_null($node)) {
        return;
    }
    echo $node->data . "\r\n";
    midOrderTraverse($node->left);
    midOrderTraverse($node->right);
}

/**
 * 中序遍历
 * @param Node $tree
 */
function midOrderTraverse($node)
{
    if (is_null($node)) {
        return;
    }
    midOrderTraverse($node->left);
    echo $node->data . "\r\n";
    midOrderTraverse($node->right);
}

/**
 * 后序遍历
 * @param Node $tree
 */
function postOrderTraverse($node)
{
    if (is_null($node)) {
        return;
    }
    midOrderTraverse($node->left);
    echo $node->data . "\r\n";
    midOrderTraverse($node->right);
}
```

> 二叉排序树
```
<?php
class Node
{
    public $data;
    public $left = null;
    public $right = null;
    public function __construct($data)
    {
        $this->data = $data;
    }
}
class Tree
{
    private $tree;

    public function getTree()
    {
        return $this->tree;
    }

    // 插入节点
    public function insert(int $data)
    {
        if (!$this->tree) {
            $this->tree = new Node($data);
            return;
        }
        $p = $this->tree;
        while ($p) {
            // 要插入的值 小于 根节点，插入左子树
            if ($data < $p->data) {
                // 如果左子树不存在，创建左子树
                if (!$p->left) {
                    $p->left = new Node($data);
                    return;
                }
                // 左子树已存在，则以左子树为根节点继续向下遍历
                $p = $p->left;
            }
            // 要插入的值 大于 根节点，插入右子树
            elseif ($data > $p->data) {
                // 如果右子树不存在，创建右子树
                if (!$p->right) {
                    $p->right = new Node($data);
                    return;
                }
                // 左子树已存在，则以左子树为根节点继续向下遍历
                $p = $p->right;
            }
        }
    }

    // 查找节点
    public function find(int $data)
    {
        $p = $this->tree;
        while ($p) {
            if ($data < $p->data) {
                $p = $p->left;
            } elseif ($data > $p->data) {
                $p = $p->right;
            } else {
                return $p;
            }
        }
        return null;
    }

    // 删除节点（这里注意看一下）
    public function delete(int $data)
    {
        if (!$this->tree) {
            return;
        }

        $p = $this->tree;
        $pp = null;         // p的父节点
        // 查找待删除节点
        while ($p && $p->data != $data) {
            $pp = $p;
            if ($p->data > $data) {
                $p = $p->left;
            } else {
                $p = $p->right;
            }
        }
        // 节点不存在
        if ($p == null) {
            return;
        }

        // 待删除节点有两个子节点
        if ($p->left && $p->right) {
            $minP = $p->right;  // 右子树的最小节点
            $minPP = $p;  // $minP的父节点
            // 查找右子树种的最小节点
            while ($minP->left) {
                $minPP = $minP;
                $minP = $minP->left;
            }
            // 用右子树最小节点，覆盖 要删除的节点
            $p->data = $minP->data;
            // 接下来就变成要删除 $minP 了
            $p = $minP;
            $pp = $minPP;
        }

        $child = null;
        if ($p->left) {
            $child = $p->left;
        } elseif ($p->right) {
            $child = $p->right;
        }

        if ($pp == null) {
            // 没有父节点，说明删除的是根节点
            $this->tree = $child;
        } elseif ($pp->left == $p) {
            $pp->left = $child;
        } else {
            $pp->right = $child;
        }
    }
}

// 简单测试
$nums = [33, 16, 50, 13, 18, 34, 58, 15, 17, 25, 51, 66, 19, 27, 55];
$tree = new Tree();
// 插入节点
for ($i = 0; $i < count($nums); $i++) {
    $tree->insert($nums[$i]);
}
midOrderTraverse($tree->getTree());
print_r($tree->getTree());
// 删除节点
$tree->delete(13);
$tree->delete(18);
$tree->delete(55);
print_r($tree->getTree());
```

> ## **平衡二叉树**
代码摘自-学院君。

这里仅仅完成了平衡二叉树的插入，像是平衡二叉树的删除节点，也需要不断去判断是否满足平衡二叉树的要求。

二叉排序树的插入、删除、查找时，最理想的情况下，时间复杂度是O（logn），而平衡二叉树就是这种理想情况。

平衡二叉树的优点是：

    查找性能最好，最稳定。

缺点也很明显：

    1、实现起来比较复杂 
    2、维护成本高（因其每次新增或删除节点时，都要判断剩下的节点构成的二叉排序树是否满足平衡二叉树的要求，如果不满足需要做相应的左旋右旋处理）

```
class AVLTree
{
    /**
     * 根节点
     * @var AVLNode
     */
    private $root;

    const LH = 1;   // 左子树高（高度差）
    const EH = 0;   // 等高
    const RH = -1;  // 右子树高（高度差）

    public function getTree()
    {
        return $this->root;
    }

    /**
     * @param int $data
     */
    public function insert(int $data)
    {
        $this->insert_node($data, $this->root);
    }

    /**
     * 插入节点
     * @param int $data
     * @param AVLNode $tree
     * @return bool
     */
    protected function insert_node(int $data, &$tree)
    {
        if (!$tree) {
            $tree = new AVLNode($data);
            $tree->bf = self::EH;
            return true;
        }

        if ($data < $tree->data) {
            if (!$this->insert_node($data, $tree->left)) {
                return false;
            } else {
                if (empty($tree->left->parent)) {
                    $tree->left->parent = $tree;
                }
                switch ($tree->bf) {
                    case self::LH:
                        $this->left_balance($tree);
                        return false;
                    case self::EH:
                        $tree->bf = self::LH;
                        return true;
                    case self::RH:
                        $tree->bf = self::EH;
                        return false;
                }
            }
        } else {
            if (!$this->insert_node($data, $tree->right)) {
                return false;
            } else {
                if (empty($tree->right->parent)) {
                    $tree->right->parent = $tree;
                }
                switch ($tree->bf) {
                    case self::LH:
                        $tree->bf = self::EH;
                        return false;
                    case self::EH:
                        $tree->bf = self::RH;
                        return true;
                    case self::RH:
                        $this->right_balance($tree);
                        return false;
                }
            }
        }
    }

    /**
     * 右旋操作
     * @param AVLNode $tree
     */
    protected function right_rotate(&$tree)
    {
        $subTree = $tree->left;  // 将子树的左节点作为新的子树根节点
        if ($tree->parent) {
            $subTree->parent = $tree->parent;  // 更新新子树根节点的父节点
            $left = false;
            if ($tree->parent->left == $tree) {
                $left = true;
            }
        } else {
            $subTree->parent = null;
        }
        $tree->left = $subTree->right;  // 将原来左节点的右子树挂到老的根节点的左子树
        $tree->parent = $subTree;
        $subTree->right = $tree;  // 将老的根节点作为新的根节点的右子树
        $tree = $subTree;
        if (!$tree->parent) {
            $this->root = $tree;
        } else {
            // 更新老的子树根节点父节点指针指向新的根节点
            if ($left) {
                $tree->parent->left = $tree;
            } else {
                $tree->parent->right = $tree;
            }
        }
    }

    /**
     * 左旋操作
     * @param AVLNode $tree
     */
    protected function left_rotate(&$tree)
    {
        $subTree = $tree->right;     // 逻辑和右旋正好相反
        $oldTree = clone $tree;
        if ($tree->parent) {
            $subTree->parent = $tree->parent;
            $left = true;
            if ($tree->parent->right == $tree) {
                $left = false;
            }
        } else {
            $subTree->parent = null;
        }
        $tree->right = $subTree->left;
        $tree->parent = $subTree;
        $subTree->left = $tree;
        $tree = $subTree;
        if (!$tree->parent) {
            $this->root = $tree;
        } else {
            if ($left) {
                $tree->parent->left = $tree;
            } else {
                $tree->parent->right = $tree;
            }
        }
    }

    /**
     * 左子树平衡旋转处理
     * @param AVLNode $tree
     */
    protected function left_balance(&$tree)
    {
        $subTree = $tree->left;
        switch ($subTree->bf) {
            case self::LH:
                // 新插入节点在左子节点的左子树上要做右单旋处理
                $tree->bf = $subTree->bf = self::EH;
                $this->right_rotate($tree);
                break;
            case self::RH:
                // 新插入节点在左子节点的右子树上要做双旋处理
                $subTree_r = $subTree->right;
                switch ($subTree_r->bf) {
                    case self::LH:
                        $tree->bf = self::RH;
                        $subTree->bf = self::EH;
                        break;
                    case self::EH:
                        $tree->bf = $subTree->bf = self::EH;
                        break;
                    case self::RH:
                        $tree->bf = self::EH;
                        $subTree->bf = self::LH;
                        break;
                }
                $subTree_r->bf = self::EH;
                $this->left_rotate($subTree);
                $this->right_rotate($tree);
        }
    }

    /**
     * 右子树平衡旋转处理
     */
    protected function right_balance(&$tree)
    {
        $subTree = $tree->right;
        switch ($subTree->bf) {
            case self::RH:
                // 新插入节点在右子节点的右子树上要做左单旋处理
                $tree->bf = $subTree->bf = self::EH;
                $this->left_rotate($tree);
                break;
            case self::LH:
                // 新插入节点在右子节点的左子树上要做双旋处理
                $subTree_l = $subTree->left;
                switch ($subTree_l->bf) {
                    case self::RH:
                        $tree->bf = self::LH;
                        $subTree->bf = self::EH;
                        break;
                    case self::EH:
                        $tree->bf = $subTree->bf = self::EH;
                        break;
                    case self::LH:
                        $tree->bf = self::EH;
                        $subTree->bf = self::RH;
                        break;
                }
                $subTree_l->bf = self::EH;
                $this->right_rotate($subTree);
                $this->left_rotate($tree);
        }
    }
}
```

测试代码
```
$avlTree = new AVLTree();
$avlTree->insert(3);
$avlTree->insert(2);
$avlTree->insert(1);
$avlTree->insert(4);
$avlTree->insert(5);
$avlTree->insert(6);
$avlTree->insert(7);
$avlTree->insert(10);
$avlTree->insert(9);
$avlTree->insert(8);
// 中序遍历生成的二叉树看是否是二叉排序树
midOrderTraverse($avlTree->getTree());
// 以数组形式打印构建的二叉树看是否是平衡二叉树
print_r($avlTree->getTree());
```
