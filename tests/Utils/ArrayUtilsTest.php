<?php
/**
 * Test case for Springy\Utils\ArrayUtils class.
 *
 * @copyright 2015 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Utils\ArrayUtils;

class ArrayUtilsTest extends TestCase
{
    protected const CONF_SESSION_TYPE = 'config.session.type';
    protected const A_LOGIN = 'A login';
    protected const A_PASSWORD = 'A password';
    protected const NAME_2 = 'Name 2';

    public $arrayUtils;
    public $data;

    const FILTERED = 'filtered';

    protected function setUp(): void
    {
        $this->arrayUtils = new ArrayUtils();
        $this->data = [
            ['key1' => 'val1', 'key2' => 'val2'],
            [
                ['name' => 'Name 1', 'language' => 'php'],
                ['name' => self::NAME_2, 'language' => 'python'],
                ['name' => 'Name 3', 'language' => 'ruby'],
            ],
            [2, 14, 5, 56, 74, 36, 23],
            [
                'config' => [
                    'db' => [
                        'mysql' => [
                            'name'  => 'mysql',
                            'login' => self::A_LOGIN,
                            'pass'  => self::A_PASSWORD,
                        ],
                        'postgre' => [
                            'name'  => 'postgre',
                            'login' => self::A_LOGIN,
                            'pass'  => self::A_PASSWORD,
                        ],
                    ],
                    'session' => [
                        'type'    => 'mysql',
                        'expires' => 3600,
                    ],
                ],
            ],
        ];
    }

    public function testAddsANewValueOnlyIfKeyIsNotAlreadySet()
    {
        $expected = ['key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3'];

        $actual = $this->arrayUtils->add($expected, 'key3', 'val3');

        $this->assertEquals($expected, $actual);

        $actual = $this->arrayUtils->add($expected, 'key3', 'changed');

        $this->assertEquals($expected['key3'], $actual['key3']);
    }

    public function testMakesAnArrayWithAFilterFunction()
    {
        $list = $this->data[0];

        $expected = [
            'key1' => self::FILTERED,
            'key2' => self::FILTERED,
        ];

        $actual = $this->arrayUtils->make($list, function ($key, $val) {
            if ($val == self::FILTERED) {
                // Only to resolves code quality issue.
            }

            return [$key, self::FILTERED];
        });

        $this->assertEquals($expected, $actual);
    }

    public function testPluckElementsOfAnArray()
    {
        $list = $this->data[1];

        $expected = ['php', 'python', 'ruby'];

        $actual = $this->arrayUtils->pluck($list, 'language');

        $this->assertEquals($expected, $actual);

        $expected = ['Name 1' => 'php', self::NAME_2 => 'python', 'Name 3' => 'ruby'];

        $actual = $this->arrayUtils->pluck($list, 'language', 'name');

        $this->assertEquals($expected, $actual);
    }

    public function testSplitsAnArrayIntoTwo()
    {
        $list = $this->data[0];

        $expectedKeys = ['key1', 'key2'];
        $expectedValues = ['val1', 'val2'];

        list($actualKeys, $actualValues) = $this->arrayUtils->split($list);

        $this->assertEquals($expectedKeys, $actualKeys);
        $this->assertEquals($expectedValues, $actualValues);
    }

    public function testReturnsOnlyTheValuesThatMatchesTheGivenKeys()
    {
        $list = $this->data[0];

        $expected = ['key2' => 'val2'];

        $actual = $this->arrayUtils->only($list, ['key2']);

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsEveryValueExcpectTheOnesThatMatchesTheGivenKeys()
    {
        $list = $this->data[0];

        $expected = ['key2' => 'val2'];

        $actual = $this->arrayUtils->except($list, ['key1']);

        $this->assertEquals($expected, $actual);
    }

    public function testSortsTheArrayValuesUsingAUserDefinedFunction()
    {
        $list = $this->data[2];

        $expected = [2, 5, 14, 23, 36, 56, 74];

        $actual = $this->arrayUtils->sort($list, function ($val1, $val2) {
            if ($val1 == $val2) {
                return 0;
            }

            return ($val1 < $val2) ? -1 : 1;
        });

        $this->assertEquals($expected, array_values($actual));
    }

    public function testReturnsTheFirstValueThatPassTheTestFunction()
    {
        $list = $this->data[1];

        $expected = ['name' => self::NAME_2, 'language' => 'python'];

        $actual = $this->arrayUtils->firstThatPasses($list, function ($key, $val) {
            return $key == 1 && $val['language'] == 'python';
        });

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsTheLastValueThatPassTheTestFunction()
    {
        $list = $this->data[2];

        $expected = 74;

        $actual = $this->arrayUtils->lastThatPasses($list, function ($key, $val) {
            return $val > 50 && !is_null($key);
        });

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsAllTheValuesThatPAssesTheTestFunction()
    {
        $list = $this->data[2];

        $expected = [56, 74];

        $actual = $this->arrayUtils->allThatPasses($list, function ($key, $val) {
            return $val > 50 && !is_null($key);
        });

        $this->assertEquals($expected, array_values($actual));
    }

    public function testItFlattensAMultidimensionalArray()
    {
        $list = $this->data[3];

        $expected = ['mysql', self::A_LOGIN, self::A_PASSWORD, 'postgre', self::A_LOGIN, self::A_PASSWORD];

        $actual = $this->arrayUtils->flatten($list['config']['db']);

        $this->assertEquals($expected, $actual);
    }

    public function testFlattensTheArrayKeepingTheHierarchyUsingTheDotNotation()
    {
        $list = $this->data[3];

        $flattenedArray = $this->arrayUtils->dottedMake($list);

        $expected = $list['config']['db']['mysql']['name'];

        $actual = $flattenedArray['config.db.mysql.name'];

        $this->assertEquals($expected, $actual);
    }

    public function testGetsAValueFromAMultidimensionalArrayUsingTheDotNotationWihoutChangingTheArray()
    {
        $list = $this->data[3];

        $expected = $list['config']['session']['type'];

        $actual = $this->arrayUtils->dottedGet($list, self::CONF_SESSION_TYPE);

        $this->assertEquals($expected, $actual);
    }

    public function testPullsAValueFromAMultidimensionalArrayUsingTheDotNotationRemovingItFromTheArray()
    {
        $list = $this->data[3];

        $expected = $list['config']['session']['type'];

        $actual = $this->arrayUtils->dottedPull($list, self::CONF_SESSION_TYPE);

        $this->assertEquals($expected, $actual);

        $this->assertFalse(isset($list['config']['session']['type']));
    }

    public function testSetsOrChangesAValueFromAMultidimensionalArrayUsingTheDotNotation()
    {
        $list = $this->data[3];

        //Changing
        $expected = 'cookie';

        $this->arrayUtils->dottedSet($list, self::CONF_SESSION_TYPE, $expected);

        $actual = $list['config']['session']['type'];

        $this->assertEquals($expected, $actual);

        //Adding
        $expected = 'redis';

        $this->arrayUtils->dottedSet($list, 'config.cache.driver.type', $expected);

        $actual = $list['config']['cache']['driver']['type'];

        $this->assertEquals($expected, $actual);
    }

    public function testUnsetsAValueFromAMuiltidimensionalArrayUsingTheDotNotation()
    {
        $list = $this->data[3];

        $this->arrayUtils->dottedUnset($list, 'config.db.postgre');

        $this->assertFalse(isset($list['config']['db']['postgre']));
    }

    public function testFetchesAPartOfAnArrayAndFlattenItUsingTheDotNotation()
    {
        $list = $this->data[3];

        $expected = [$list['config']['db']['mysql']];

        $actual = $this->arrayUtils->dottedFetch($list, 'db.mysql');

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsAArrayUtilsInstance()
    {
        $this->assertInstanceOf(
            get_class(new ArrayUtils()),
            ArrayUtils::newInstance()
        );
    }
}
