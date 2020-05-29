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