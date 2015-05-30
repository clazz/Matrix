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
    public function testBasicSelect1() {
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
    public function testBasicSelect2() {
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
    public function testBasicSelect3() {
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
    public function testBasicSelect4() {
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
    public function testBasicSelect5() {
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
    public function testBasicSelect6() {
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

    public function testWhere1() {
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

    public function testWhere2() {
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
    public function testIndexedBy1() {
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
    public function testIndexedBy2() {
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
    public function testIndexedBy3() {
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

    public function testOrderBy1() {
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

    public function testOrderBy2() {
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

    public function testOrderBy3() {
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

    public function testOrderBy4() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->orderBy(array('id' => SORT_DESC, 'name' => SORT_ASC))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, 0, -1));
        $this->assertEquals($data, $expected);
    }

    public function testGroupBy1() {

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
    public function testMap() {
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
    public function testIterator() {

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

//        dump($query);

        $this->assertEquals(count($data), count($query));

        reset($query);
    }
}
