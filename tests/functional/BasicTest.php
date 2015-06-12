<?php
namespace tests\functional;

use \Matrix;


/**
 * 基础测试
 * Class BasicTest
 * @package tests\functional
 */
class BasicTest extends \PHPUnit_Framework_TestCase {
    /**
     * alias映射
     */
    public function test_select_alias_single() {
        $data = array(
            array('productId' => 1, 'productName' => 'test'),
        );

        $data = Matrix::from($data)
            ->select(array('productId' => 'id', 'productName' => 'name'))
            ->toArray();

        $expected = array(
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * alias映射
     */
    public function test_select_alias_multi() {
        $data = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('productId' => 1, 'productName' => 'test')
        );
        $data = Matrix::from($data)->select(array('productId' => 'id', 'productName' => 'name'))
            ->toArray();

        $expected = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * alias映射
     */
    public function test_select_no_alias() {
        $data = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $data = Matrix::from($data)->select(array('id', 'name'))
            ->toArray();

        $expected = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * alias映射
     */
    public function test_select_mixed_no_alias_and_alias() {
        $dataSource = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'productName' => 'test')
        );
        $data = Matrix::from($dataSource)
            ->select(array('id', 'productName' => 'name'))
            ->toArray();

        $expected = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * alias映射
     */
    public function test_select_mixed_alias_and_no_alias() {
        $dataSource = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('productId' => 1, 'name' => 'test')
        );
        $data = Matrix::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->toArray();

        $expected = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * 如果参数为空则表示选择所有数据
     */
    public function test_select_empty_means_all() {
        $dataSource = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $data = Matrix::from($dataSource)
            ->select(array())
            ->toArray();

        $expected = array_fill(0, TEST_MAX_ARRAY_SIZE,
            array('id' => 1, 'name' => 'test')
        );
        $this->assertEquals($data, $expected);
    }

    public function test_where_simple_match() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));
        $data = Matrix::from($dataSource)
            ->select(array())
            ->where(array('id' => 1))
            ->toArray();

        $expected = array(
            array('id' => 1, 'name' => 'test1')
        );
        $this->assertEquals($data, $expected);
    }

    public function test_where_using_closure() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        $data = Matrix::from($dataSource)
            ->select(array())
            ->where(function ($item) {
                return $item['id'] > ~~(TEST_MAX_ARRAY_SIZE / 2);
            })
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(~~(TEST_MAX_ARRAY_SIZE / 2) + 1, TEST_MAX_ARRAY_SIZE));
        $this->assertEquals($data, $expected);
    }

    /**
     * 单个key
     */
    public function test_indexedBy_single_id() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));
        $data = Matrix::from($dataSource)
            ->indexedBy('id')
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE)), function ($carry, $item) {
            $carry[$item['id']] = $item;

            return $carry;
        }, array());
        $this->assertEquals($data, $expected);
    }

    /**
     * 单个key
     */
    public function test_indexedBy_single_name() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        $data = Matrix::from($dataSource)
            ->indexedBy('name')
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE)), function ($carry, $item) {
            $carry[$item['name']] = $item;

            return $carry;
        }, array());
        $this->assertEquals($data, $expected);
    }

    /**
     * 两个key
     */
    public function test_indexedBy_multi_fields() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        $data = Matrix::from($dataSource)
            ->indexedBy(array('id', 'name'))
            ->toArray();

        $expected = array_reduce(array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE)), function ($carry, $item) {
            $carry[$item['id'] . '_' . $item['name']] = $item;

            return $carry;
        }, array());
        $this->assertEquals($data, $expected);
    }

    public function test_orderBy_single_id() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy('id')
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));
        $this->assertEquals($data, $expected);
    }

    public function test_orderBy_single_id_desc() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy('id', SORT_DESC)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, 0, -1));
        $this->assertEquals($data, $expected);
    }

    public function test_orderBy_single_id_desc_in_array_form() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy(array('id' => SORT_DESC))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, 0, -1));
        $this->assertEquals($data, $expected);
    }

    public function test_orderBy_multi_fields() {
        $dataSource = array(
            array('age' => 10, 'name' => 'Jim'),
            array('age' => 10, 'name' => 'Tom'),
            array('age' => 20, 'name' => 'Tony'),
            array('age' => 20, 'name' => 'Jack'),
        );

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy(array('age' => SORT_DESC, 'name' => SORT_ASC))
            ->toArray();

        $expected = array(
            array('age' => 20, 'name' => 'Jack'),
            array('age' => 20, 'name' => 'Tony'),
            array('age' => 10, 'name' => 'Jim'),
            array('age' => 10, 'name' => 'Tom'),
        );
        $this->assertEquals($data, $expected);

        $data = Matrix::from($dataSource)
            ->orderBy(array('age' => SORT_ASC, 'name' => SORT_DESC))
            ->toArray();

        $expected = array(
            array('age' => 10, 'name' => 'Tom'),
            array('age' => 10, 'name' => 'Jim'),
            array('age' => 20, 'name' => 'Tony'),
            array('age' => 20, 'name' => 'Jack'),
        );
        $this->assertEquals($data, $expected);
    }

    public function test_groupBy_single_class() {

        $dataSource = array_merge(
            array_map(function ($i) {
                return array('id' => $i, 'name' => 'test' . $i, 'class' => 10);
            }, range(0, 10)),
            array_map(function ($i) {
                return array('id' => $i, 'name' => 'test' . $i, 'class' => 20);
            }, range(11, 20)),
            array_map(function ($i) {
                return array('id' => $i, 'name' => 'test' . $i, 'class' => 30);
            }, range(21, 30))
        );

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy(array('id' => SORT_ASC))
            ->groupBy('class')
            ->toArray();

        $expected = array(
            10 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 10);
                }, range(0, 10)),
            20 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 20);
                }, range(11, 20)),
            30 =>
                array_map(function ($i) {
                    return array('id' => $i, 'name' => 'test' . $i, 'class' => 30);
                }, range(21, 30))
        );
        $this->assertEquals($data, $expected);
    }

    /**
     * 测试映射功能
     */
    public function test_map_single_id() {
        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->where(function ($row) {
                return $row['id'] > TEST_HALF_MAX_ARRAY_SIZE;
            })
            ->orderBy('id', SORT_DESC)
            ->indexedBy('name')
            ->map(function ($row) {
                return $row['id'];
            })
            ->toArray();

        $expected = array_reduce(
            array_map(function ($i) {
                return array(
                    'id' => $i,
                    'name' => 'test' . $i
                );
            }, range(TEST_MAX_ARRAY_SIZE, TEST_HALF_MAX_ARRAY_SIZE + 1, -1)),
            function ($carry, $item) {
                $carry[$item['name']] = $item['id'];

                return $carry;
            }, array());
        $this->assertEquals($data, $expected);
    }

    /**
     * 测试迭代器
     */
    public function test_array_access_iterator() {

        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $query = Matrix::from($dataSource)
            ->select(array('productId' => 'id', 'name'))
            ->where(function ($row) {
                return $row['id'] > TEST_HALF_MAX_ARRAY_SIZE;
            });

        $data = $query->toArray();

        foreach ($query as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }

        $this->assertEquals(count($data), count($query));

        reset($query);
    }

    public function test_orderBy_list_in_correct_form() {
        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $expected = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        $data = Matrix::from($dataSource)->orderBy(array('productId' => array_merge(array('list:'), range(0, TEST_MAX_ARRAY_SIZE))))->toArray();
        $this->assertEquals($expected, $data);
    }

    // 根据列表进行排序 - 不在列表中的排在最后
    public function test_OrderBy_list_elements_not_in_list_should_at_end() {
        $dataSource = array(
            array('gender' => 'female'),
            array('gender' => 'femazzz'),
            array('gender' => 'male'),
        );

        $data = Matrix::from($dataSource)->orderBy(array('gender' => array('list:', 'male', 'female')))->toArray();

        $expected = array(
            array('gender' => 'male'),
            array('gender' => 'female'),
            array('gender' => 'femazzz'),
        );
        $this->assertEquals($expected, $data);
    }

    public function test_orderBy_list_in_incorrect_form() {
        $dataSource = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $expected = array_map(function ($i) {
            return array(
                'productId' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'other' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        $data = Matrix::from($dataSource)->orderBy(array('productId' => array_merge(array('list'), range(0, TEST_MAX_ARRAY_SIZE))))->toArray();
        $this->assertNotEquals($expected, $data);
    }

    public function test_isAll(){
        $this->assertEquals(true, Matrix::from(array(1, 2, 3))->isAll(function($x){ return $x > 0; }));
        $this->assertEquals(true, Matrix::from(array())->isAll(function($x){ return $x > 0; }));
        $this->assertEquals(false, Matrix::from(array(1, 2, 3))->isAll(function($x){ return $x < 1; }));
        $this->assertEquals(false, Matrix::from(array(1, 2, 3))->isAll(function($x){ return $x < 2; }));
        $this->assertEquals(false, Matrix::from(array(1, 2, 3))->isAll(function($x){ return $x < 3; }));
    }

    public function test_isAny() {
        $this->assertEquals(true, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x > 0;}));
        $this->assertEquals(false, Matrix::from(array())->isAny(function($x){ return $x > 0; }));
        $this->assertEquals(false, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x < 0; }));
        $this->assertEquals(false, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x < 1; }));
        $this->assertEquals(true, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x < 2; }));
        $this->assertEquals(true, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x < 3; }));
        $this->assertEquals(true, Matrix::from(array(1, 2, 3))->isAny(function($x){ return $x < 4; }));
    }

    public function test_uniqueBy_no_preserve_keys() {
        $data = Matrix::from(array(
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
        ))
            ->uniqueBy('id')->toArray();
        $expected = array(
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
        );
        $this->assertEquals($expected, $data);
    }

    public function test_uniqueBy_with_preserve_keys_of_id() {
        $data = Matrix::from(array(
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
        ))
            ->uniqueBy('id', true)->toArray();
        $expected = array(
            '1' => array('id' => 1, 'name' => 'Jim'),
            '2' => array('id' => 2, 'name' => 'Tom'),
        );
        $this->assertEquals($expected, $data);
    }

    public function test_uniqueBy_with_preserve_keys_of_name() {
        $data = Matrix::from(array(
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
            array('id' => 1, 'name' => 'Jim'),
            array('id' => 2, 'name' => 'Tom'),
        ))
            ->uniqueBy('name', true)->toArray();
        $expected = array(
            'Jim' => array('id' => 1, 'name' => 'Jim'),
            'Tom' => array('id' => 2, 'name' => 'Tom'),
        );
        $this->assertEquals($expected, $data);
    }
}
