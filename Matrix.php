<?php
/**
 * 矩阵(即二维数据/数据表)数据查询类，提供一些函数式的查询接口(未完待续）
 * Class Matrix
 * 使用实例：
 * // 假如有个数据源有productId和productName两列，而希望将productId大于5的数据提出取出来，并且组织成productName => [ xxx ]的形式，则可以：
 * $data = Matrix::from($dataSource)
 *          ->select(array('productId' => 'id', 'productName'))
 *          ->where(function ($row) {
 *              return $row['id'] > 5;
 *          }, Matrix::CONDITION_CUSTOM)
 *          ->orderBy('id', SORT_DESC)
 *          ->indexedBy('productName')
 *          ->toArray();
 * 注意1：假定矩阵是行优先存储，即第X行第Y列存放在[Y][X]的位置
 * 注意2：此类已经实现类似Array的下标、遍历和计数等操作，几乎可以直接当作Array来操作。
 */
class Matrix implements IteratorAggregate,ArrayAccess,Countable {
    /**
     * 不允许直接new，应使用Matrix::from()来创建实例
     */
    private function __construct(){

    }

    /**
     * 指定数据源
     * @param $dataSource mixed 可以是array或其他格式类似数据库表的数据
     * @return Matrix
     */
    public static function from($dataSource){
        $instance = new Matrix();
        $instance->data = $dataSource;
        return $instance;
    }

    /**
     * 返回一个空的查询
     * @return Matrix
     */
    public static function emptyQuery(){
        static $emptyCollection = null;

        if ($emptyCollection === null){
            $emptyCollection = self::from(array());
        }

        return $emptyCollection;
    }

    /**
     * 合并数据源
     * @param $dataSource array
     * @return $this
     */
    public function unionAll($dataSource){
        if (!is_array($dataSource)){
            $dataSource = Matrix::from($dataSource)->toArray();
        }

        if (!is_array($this->data)){
            $this->data = $this->toArray();
        }

        $this->data = array_merge($this->data, $dataSource);
        return $this;
    }

    /**
     * 选择哪些数据 (如果参数为空则表示选择所有数据)
     * @param $keyNames array 指定数据行的key名称，有两种形式：
     * 1. 保持key不变，如 array('id', 'name')
     * 2. 映射key的别名, 如 array('productId' => 'id' , 'productName' => 'name') 表示将原key名productId映射为id并选取
     * 两种形式可以混合使用，比如： array( 'productId' => 'id', 'name')，表示选取原productId为id，并选取name
     * @return Matrix
     */
    public function select($keyNames){
        if (empty($keyNames)){
            return $this;
        }

        // 统一形式为 key => alias的格式
        $keyAliasMap = array();
        foreach ($keyNames as $key => $alias) {
            // 数字的key，说明不需要映射别名
            if (is_int($key)){
                $keyAliasMap[$alias] = $alias;
            }
            // 需要映射别名
            else {
                $keyAliasMap[$key] = $alias;
            }
        }

        unset($keyNames);

        // 转换数据
        $selectedData = array();
        foreach ($this->data as $rowData) {
            $selectedItem = array();
            foreach ($keyAliasMap as $key => $alias) {
                $selectedItem[$alias] = $rowData[$key];
            }
            $selectedData[] = $selectedItem;
        }

        $this->data = $selectedData;
        return $this;
    }

    public function groupSelect($keyNames){
        $selected = array();
        foreach ($this->data as $groupName => $groupData) {
            $selected[$groupName] = Matrix::from($groupData)->select($keyNames)->toArray();
        }

        $this->data = $selected;
        return $this;
    }

    /**
     * 映射
     * @param callable $mapFunc 将每一行数据映射为$mapFunc()的返回值, 函数入参即行的数据，行的key
     * @return $this
     */
    public function map($mapFunc){
        foreach ($this->data as $key => &$rowData) {
            $rowData = call_user_func($mapFunc, $rowData, $key);
        }
        return $this;
    }

    /**
     * 指定过滤条件
     * @param $conditions mixed 参见self::CONDITION_XXX
     * @param $conditionType int 指定条件的类型
     * @return Matrix
     * @throws InvalidArgumentException
     */
    public function where($conditions, $conditionType = self::CONDITION_SIMPLE_MATCH){
        // 为空表示全部数据
        if (empty($conditions)){
            return $this;
        }

        //　如果传进来的是闭包，则将是自定义匹配
        if ($conditions instanceof Closure){
            $conditionType = self::CONDITION_CUSTOM;
        }

        $filteredData = array();

        // 过滤数据
        switch ($conditionType){
            case self::CONDITION_SIMPLE_MATCH:
                foreach ($this->data as $rowData) {
                    // 判断是否匹配所有的条件
                    $isMatch = true;
                    foreach ($conditions as $key => $conditionValue) {
                        if ($rowData[$key] != $conditionValue){
                            $isMatch = false;
                        }
                    }

                    // 匹配则是需要的数据
                    if ($isMatch){
                        $filteredData[] = $rowData;
                    }
                }
                break;

            case self::CONDITION_STRICT_MATCH:
                foreach ($this->data as $rowData) {
                    // 判断是否匹配所有的条件
                    $isMatch = true;
                    foreach ($conditions as $key => $conditionValue) {
                        if ($rowData[$key] != $conditionValue){
                            $isMatch = false;
                        }
                    }

                    // 匹配则是需要的数据
                    if ($isMatch){
                        $filteredData[] = $rowData;
                    }
                }
                break;

            case self::CONDITION_CUSTOM:
                // 过滤数据
                foreach ($this->data as $rowData) {
                    // 匹配则是需要的数据
                    if (call_user_func($conditions, $rowData)){
                        $filteredData[] = $rowData;
                    }
                }
                break;
            default:
                throw new InvalidArgumentException("Invalid condition type: $conditionType.");
        }

        $this->data = $filteredData;
        return $this;
    }

    /**
     * 以$keyName为KEY将数据组织成 $key => $row的形式
     * @param $columnName mixed
     * 1. string 指定某个列为key
     * 2. array 指定多个列为key，key的组合方式是使用空字符$keyNameGlue为间隔符
     * @param $keyNameGlue string 当指定多个列为key时，使用的间隔符
     * @return Matrix
     */
    public function indexedBy($columnName, $keyNameGlue = "_"){
        $indexedData = array();

        if (!is_array($columnName)){
            // 将数据转换为以$keyName那一列为key的数组
            foreach ($this->data as $rowData) {
                $indexedData[$rowData[$columnName]] = $rowData;
            }
        } else {
            // key的组合方式是使用空字符chr(0)为间隔符
            foreach ($this->data as $rowData) {
                $keys = array();
                foreach ($columnName as $key) {
                    $keys[] = $rowData[$key];
                }
                $indexedData[implode($keyNameGlue, $keys)] = $rowData;
            }
        }

        $this->data = $indexedData;

        return $this;
    }

    /**
     * 按某一列或某几列进行唯一化
     * @param string|array $columnName
     * @param bool $preserveKeys  是否保留key
     * @return $this
     */
    public function uniqueBy($columnName, $preserveKeys=false){
        $this->indexedBy($columnName);
        if (!$preserveKeys){
            $this->data = $this->values();
        }
        return $this;
    }

    /**
     * 根据列来将数据进行分组
     * @param  mixed $columnName 列名
     * 1. string 指定某个列为key
     * 2. array 指定多个列为key，key的组合方式是使用空字符$keyNameGlue为间隔符
     * @param string $groupKeyGlue
     * @return $this
     */
    public function groupBy($columnName, $groupKeyGlue = "_"){
        $groupedData = array();

        if (!is_array($columnName)){
            // 将数据转换为以$keyName那一列为key的数组
            foreach ($this->data as $rowData) {
                $groupKey = $rowData[$columnName];
                if (!isset($groupedData[$groupKey])){
                    $groupedData[$groupKey] = array();
                }
                $groupedData[$groupKey][] = $rowData;
            }
        } else {
            // key的组合方式是使用空字符chr(0)为间隔符
            foreach ($this->data as $rowData) {
                $keys = array();
                foreach ($columnName as $key) {
                    $keys[] = $rowData[$key];
                }

                // 构造分组的key
                $groupKey = implode($groupKeyGlue, $keys);

                if (!isset($groupedData[$groupKey])){
                    $groupedData[$groupKey] = array();
                }
                $groupedData[$groupKey][] = $rowData;
            }
        }

        $this->data = $groupedData;
        return $this;
    }

    /**
     * 排序
     * @param $keyNames mixed
     * 1. string 根据单个key进行排序
     * 2. array 指定多个key进行排序，形如 array('id', 'name') 或
     *      array(
     *            // 排序类型或排序标志：
     *           'id' => array( SORT_ASC/SORT_DESC, SORT_REGULAR/SORT_NUMERIC/SORT_STRING),
     *           'name' => array(SORT_ASC),
     *           'age' => SORT_NUMERIC,
     *           // 按照某个列表进行排序，"list:"是前导标志；
     *           // 不在列表中的值排在最后
     *           'gender' => array('list:', 'male', 'female)
     *      )
     * @param $orderType int SORT_ASC/SORT_DESC
     * @param $sortFlag int SORT_REGULAR/SORT_NUMERIC/SORT_STRING
     * @return Matrix
     * @throws InvalidArgumentException
     */
    public function orderBy($keyNames, $orderType = SORT_ASC, $sortFlag = SORT_REGULAR){
        // 先转化为数组
        $this->data = $this->toArray();

        // 多个key进行排序
        if (is_array($keyNames)){
            // 构造array_multisort的参数
            $args = array();
            foreach ($keyNames as $key => $option) {
                // 数字key，说明$option其实是列名
                if (is_int($key)){
                    $args[] = Matrix::from($this->data)->column($option);
                    $args[] = $orderType;
                    $args[] = $sortFlag;
                } else if ($option[0] === 'list:'){ // 按照列表进行排序
                    array_shift($option);
                    $args[] = $this->duplicate()->map(function($row) use($key, $option) {
                        $index = array_search($row[$key], $option);
                        return $index === false ? PHP_INT_MAX : $index;
                    })->toArray();
                    $args[] = $orderType;
                    $args[] = $sortFlag;
                } else {
                    $args[] = Matrix::from($this->data)->column($key);

                    // 只有一个元素，简化处理
                    if (is_array($option) && count($option) == 1){
                        $option = reset($option);
                    }

                    // 增加排序类型和排序标志参数
                    if (is_array($option)){
                        $args[] = $option[0];
                        $args[] = $option[1];
                    } else {
                        if (in_array($option, array(SORT_ASC, SORT_DESC))){
                            $args[] = $option;
                            $args[] = $sortFlag;
                        } else {
                            $args[] = $orderType;
                            $args[] = $sortFlag;
                        }
                    }
                }
            }

//            $args[] = &$this->data;
//            call_user_func_array('array_multisort', $args);   // call_user_func_array seems won't change data! (It's a bug under PHP3.3.3. However, I have to work with it!)
            switch (count($keyNames)){
                case 1:
                    array_multisort($args[0], $args[1], $args[2], $this->data);
                    break;
                case 2:
                    array_multisort($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $this->data);
                    break;
                case 3:
                    array_multisort($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $this->data);
                    break;
                default:
                    throw new InvalidArgumentException('Too many sort keys!');
            }
        }
        // 单个key进行排序
        else {
            $sortColumnData = Matrix::from($this->data)->column($keyNames);
            array_multisort($sortColumnData, $orderType, $sortFlag, $this->data);
        }
        return $this;
    }

    /**
     * 获取第一列
     * @return array
     */
    public function firstColumn(){
        $data = $this->toArray();
        $data = array_map(function($rowData){
            return reset($rowData);
        }, $data);
        return $data;
    }

    /**
     * 获取某一列
     * @param $columnName string 列名
     * @return array
     */
    public function column($columnName){
        $data = $this->toArray();
        $data = array_map(function($rowData) use ($columnName){
            return $rowData[$columnName];
        }, $data);
        return $data;
    }

    /**
     * 返回某一行
     * @param $rowIndex mixed
     * @return array
     */
    public function row($rowIndex){
        $data = $this->toArray();
        return $data[$rowIndex];
    }

    /**
     * 返回第一行
     * @return array
     */
    public function firstRow() {
        $data = $this->toArray();
        return reset($data);
    }

    /**
     * 转化为数组
     * @param bool $associated  行数据是否是关联数组
     * @return array
     * @throws InvalidArgumentException
     */
    public function toArray($associated=true){
        if (is_array($this->data)){
            if ($associated){
                return $this->data;
            } else {
                return array_map(function($rowData){
                    return array_values($rowData);
                }, $this->data);
            }
        }

        $arr = array();
        if ($associated){
            foreach ($this->data as $rowData) {
                $arr[] = $rowData;
            }
        } else {
            foreach ($this->data as $rowData) {
                $arr[] = array_values($rowData);
            }
        }
        return $arr;
    }

    /**
     * 判断是否为空
     * @return bool
     */
    public function isEmpty(){
        if ($this->data == null) {
            return true;
        }
        return 0 == $this->count();
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @implements IteratorAggregate::getIterator()
     */
    public function getIterator(){
        return new MatrixIterator($this->data);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @implements ArrayAccess::offsetExists
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @implements ArrayAccess::offsetGet
     */
    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @implements ArrayAccess::offsetSet
     */
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @implements ArrayAccess::offsetUnset
     */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
     * @return int 行数
     * @implements Countable::count()
     */
    public function count() {
        return count($this->data);
    }

    /**
     * 返回所有列的key，无value
     * @return array
     */
    public function keys(){
        return array_keys($this->toArray());
    }

    /**
     * 返回所有列的值，无key
     * @return array
     */
    public function values(){
        return array_values($this->toArray());
    }

    /**
     * 取几个元素
     * @param int $limit 取几个
     * @param int $start 从第几个开始取
     * @return $this
     */
    public function take($limit, $start=0) {
        $this->data = array_slice($this->toArray(), $start, $limit);
        return $this;
    }

    /**
     * @return Matrix 返回复制品
     */
    public function duplicate(){
        return clone $this;
    }

    /**
     * 判断是否全都符合某个条件
     * @param $condition callable 条件, 第一个参数是row，第二个是index
     * @return bool
     */
    public function isAll($condition){
        foreach ($this->data as $index => $row) {
            if (!call_user_func($condition, $row, $index)){
                return false;
            }
        }
        return true;
    }

    /**
     * 判断是否有一个符合某个条件
     * @param $condition callable 条件, 第一个参数是row，第二个是index
     * @return bool
     */
    public function isAny($condition){
        foreach ($this->data as $index => $row) {
            if (call_user_func($condition, $row, $index)){
                return true;
            }
        }
        return false;
    }

    // 简单匹配，$conditions形如array('key' => $value, ...), 用于匹配data[key] == $value的数据
    const CONDITION_SIMPLE_MATCH = 0;
    // 严格匹配，$conditions类似简单匹配的格式，但是执行的是===的严格匹配
    const CONDITION_STRICT_MATCH = 1;
    // 自定义匹配，$conditions是可以调用的函数，形如bool function($rowData), 返回true表示要命中的数据
    const CONDITION_CUSTOM = 2;

    // 矩阵数据
    private $data;
}

/**
 * 针对Matrix的迭代器
 * Class MatrixIterator
 */
class MatrixIterator implements Iterator{

    public function __construct(&$tableData){
        $this->tableData = &$tableData;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(){
        return current($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(){
        next($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key(){
        return key($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(){
        return isset($this->tableData[key($this->tableData)]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(){
        rewind($this->tableData);
    }


    private $tableData;
}