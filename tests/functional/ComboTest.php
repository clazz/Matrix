<?php


namespace tests\functional;
use \Matrix;

/**
 * 组合测试
 * Class ComboTest
 * @package tests\functional
 */
class ComboTest extends \PHPUnit_Framework_TestCase
{

    /*
     * 组合测试
     */
    public function testCombo0() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'akkka' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->select(array('id', 'name'))
            ->orderBy('id', SORT_DESC)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, 0, -1));
        $this->assertEquals($expected, $data);
    }

    /*
     * 组合测试
     */
    public function testCombo1() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'akkka' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->select(array('id', 'name'))
            ->orderBy(array('id' => SORT_DESC, 'name' => SORT_ASC))
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, 0, -1));
        $this->assertEquals($expected, $data);
    }

    /*
     * 组合测试
     */
    public function testCombo2() {
        $dataSource = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i,
                'otherFields' => 'akkka' . $i
            );
        }, range(0, TEST_MAX_ARRAY_SIZE));

        shuffle($dataSource);

        $data = Matrix::from($dataSource)
            ->select(array('id', 'name'))
            ->where(function ($row) {
                return $row['id'] > TEST_HALF_MAX_ARRAY_SIZE;
            })
            ->orderBy('id', SORT_DESC)
            ->toArray();

        $expected = array_map(function ($i) {
            return array(
                'id' => $i,
                'name' => 'test' . $i
            );
        }, range(TEST_MAX_ARRAY_SIZE, TEST_HALF_MAX_ARRAY_SIZE + 1, -1));
        $this->assertEquals($data, $expected);
    }

    /**
     * 组合测试3
     */
    public function testCombo3() {
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
            ->toArray();

        $expected = array_reduce(
            array_map(function ($i) {
                return array(
                    'id' => $i,
                    'name' => 'test' . $i
                );
            }, range(TEST_MAX_ARRAY_SIZE, TEST_HALF_MAX_ARRAY_SIZE + 1, -1)),
            function ($carry, $item) {
                $carry[$item['name']] = $item;

                return $carry;
            }, array());
        $this->assertEquals($data, $expected);
    }

} 