# 矩阵（Matrix）
矩阵（Matrix）——又是一个操作数组（特别是二维数组）的工具。

# 使用说明
假如有个数据源有productId和productName两列，而希望将productId大于5的数据提出取出来，并且组织成productName => [ xxx ]的形式，则可以：

```
$data = Matrix::from($dataSource)
      ->select(array('productId' => 'id', 'productName'))
      ->where(function ($row) {
          return $row['id'] > 5;
      })
      ->orderBy('id', SORT_DESC)
      ->indexedBy('productName')
      ->toArray();
```

注意1：假定矩阵是行优先存储，即第X行第Y列存放在[Y][X]的位置
注意2：此类已经实现类似Array的下标、遍历和计数等操作，几乎可以直接当作Array来操作。