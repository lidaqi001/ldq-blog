# 算法

> ## **排序算法**

> 冒泡排序
```php
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
```php

```

> 选择排序
```php

```

> 快速排序
```php
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

> 归并排序（原地排序版-实现复杂）
```php

```

> 归并排序（额外空间版-实现简单）
```php


```

> ## **查找算法**

> 二分查找
```php
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

<!-- > ## **二叉树** -->

> ## **二叉树遍历**

```php
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

> ## **二叉排序树**

不论是插入、删除、还是查找，二叉排序树的**时间复杂度都等于二叉树的高度**，***最好的情况***当然是满二叉树或完全二叉树，此时根据完全二叉树的特性，时间复杂度是 **O(logn)**，性能相当好，***最差的情况***是二叉排序树退化为线性表（斜树），此时的时间复杂度是 **O(n)**，所以二叉排序树的形状也很重要，**不同的形状会影响最终的操作性能**
```php
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

```php
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
```php
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

> ## **红黑树**

好复杂，没搞懂。。。

摘自学院君-
```text
> 什么是红黑树

红黑树（Red-Black Tree）是每个节点都带有颜色属性的二叉排序（查找）树，具备以下特性：

- 节点是红色或黑色；
- 根节点是黑色的；
- 每个叶子节点都是黑色的空节点（NIL），也就是说，叶子节点不存储数据；
- 任何相邻的节点都不能同时为红色，也就是说，红色节点是被黑色节点隔开的；
- 每个节点，从该节点到达其可达叶子节点的所有路径，都包含相同数目的黑色节点；

下面是一个典型的红黑树示例：
```
![image](./images/arithmetic/red-black-tree.jpg)
```text
这些约束保证了红黑树的关键特性：从根节点到叶子节点的最长的可能路径不多于最短的可能路径长度的两倍（每条路径红黑相间，且黑色节点数目相同，所以最短的路径上是两个黑色节点，相应的，此时最长路径节点一定是黑-红-黑-红，正好是其两倍），从而保证红黑树无论怎么插入、删除节点大致上也是平衡的。

> 红黑树的算法复杂度
我们上面提到由于红黑树的特性，可以确保即使在最差情况下，红黑树也是大致平衡的，下面我们来简单推导下红黑树的时间复杂度。

前面我们讲二叉排序树的时候说到二叉排序树的时间复杂度和树的高度成正比，红黑树是红黑相间的，我们可以先把红色的节点去掉，剩下的黑色节点就可能变成四叉树了，比如我们上面示例的那个红黑树。由于红黑树每条路径上黑色节点相同，所以可以继续把这个四叉树转化为完全二叉树，假设黑色节点的数量为 m，这样，这棵树的时间复杂度就是 O(logm) 了；然后我们把红色节点塞回来，红色节点的总数目肯定是小于等于黑色节点的，我们不妨假设等于黑色节点，这样，树的高度就增加一倍，对应的时间复杂度就是 2O(logm) 了，m≈n/2，由于在计算时间复杂度的时候，常量可以舍弃，所以红黑树的时间复杂度也是 O(logn)。虽然这里面都是估算的，但是由于前面提到的红黑树的特性约束，数量级上是没问题的。

> 为什么工程上大多使用红黑树
红黑树维护成本比平衡二叉树低，性能上也能大致做到 O(logn)，且比较稳定，可以应付最差的情况。下一篇我们就来简单介绍下红黑树的实现。·
```

> ## **堆排序**（大顶堆/小顶堆）

- 什么是堆

```text
堆是一种特殊的二叉树，具备以下特性：

    - 堆是一个完全二叉树
    - 每个节点的值都必须大于等于（或小于等于）其左右孩子节点的值

如果每个节点的值都大于等于左右孩子节点的值，这样的堆叫大顶堆；如果每个节点的值都小于等于左右孩子节点的值，这样的堆叫小顶堆。
```

- 构建堆

```text
下面我们就来看如何在堆中插入新节点，以大顶堆为例，从叶子结点插入，如果比父级元素大，则与父级元素交换位置，依次类推，直到到达根节点（小顶堆恰好相反）： 
```

![image](./images/arithmetic/insert-heap.png)

- 堆排序

```text
堆排序的过程其实就是不断删除堆顶元素的过程。如果构建的是大顶堆，逐一删除后堆顶元素构成的序列是从大到小排序；如果构建的是小顶堆，逐一删除堆顶元素后构成的序列是从小到大排序。而这其中的原理，就是我们在上一篇提到的：对于大顶堆，堆顶一定是最大值；对于小顶堆，堆顶一定是最小值。

但是这里有一个问题，每次从堆顶删除元素后，需要从子节点中取值补齐堆顶，依次类推，直到叶子节点，就会致使存储堆的数组出现「空洞」：
```

![image](./images/arithmetic/heap-sort-1.png)

```text
解决办法是将数组中的最后一个元素（最右边的叶子节点）移到堆顶，再重新对其进行堆化：
```

![image](./images/arithmetic/heap-sort-2.png)

```php
<?php

class Heap
{
    private $arr = [];
    private $n;
    private $count;
    private $removeN;

    public function __construct($capacity = 10)
    {
        $this->n = $capacity;
        $this->count = 0;
    }

    // 如何在堆中插入新节点，以大顶堆为例，从叶子结点插入，如果比父级元素大，则与父级元素交换位置，依次类推，直到到达根节点（小顶堆恰好相反）
    public function insert($data)
    {
        $this->count++;
        $this->arr[$this->count] = $data;
        $i = $this->count;
        // echo intval($i / 2);
        while (intval($i / 2) > 0 && $this->arr[intval($i / 2)] < $this->arr[$i]) {
            // 交换
            $this->swap($i, intval($i / 2));
            $i = $i / 2;
        }
        // print_r($this->arr);
        // exit;
        return true;
    }

    // 由于完全二叉树的特殊性，可以通过数组来存储，堆也是完全二叉树，所以我们完全可以通过数组来存储。在使用数组存储堆的时候，把第一个索引位置留空，从第二个索引位置开始存储堆元素，这样，对于索引值为 i 的元素而言，其子节点索引分别为 2i 和 2i+1。
    // 依次移除堆顶，同时将数组中的最后一个元素（最右边的叶子节点）移到堆顶，再重新对其进行堆化（使其始终符合堆定义，大顶堆、小顶堆）
    public function remove()
    {
        if ($this->count == 0 || $this->removeN >= $this->n)
            return false;
        $removeData = $this->arr[1];
        $this->arr[1] = $this->arr[$this->count];
        $this->count--;
        $this->removeN++;
        $i = 1;  // 堆顶元素
        while (true) {
            $maxPos = $i;
            if ($i * 2 <= $this->count && $this->arr[$i * 2] > $this->arr[$i]) {
                $maxPos = 2 * $i;
            }
            if ($i * 2 + 1 <= $this->count && $this->arr[$i * 2 + 1] > $this->arr[$maxPos]) {
                $maxPos = 2 * $i + 1;
            }
            if ($maxPos == $i) {
                break;
            }
            // 交换
            $this->swap($i, $maxPos);
            $i = $maxPos;
        }
        return $removeData;
    }

    public function swap($a, $b)
    {
        $temp = $this->arr[$a];
        $this->arr[$a] = $this->arr[$b];
        $this->arr[$b] = $temp;
    }

    public function __toString()
    {
        return json_encode(array_values($this->arr));
    }
}

// 测试代码
$heap = new Heap();
$data = range(1, 20);
shuffle($data);
// 堆化
foreach ($data as $num) {
    if (!$heap->insert($num)) {
        break;
    }
}
// 排序输出
$sortedData = [];
while ($removedData = $heap->remove()) {
    $sortedData[] = $removedData;
}
print_r($sortedData);

```