<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Tests\Validator package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Validator;

use \Thapp\JitImage\Validator\ModeConstraints;

/**
 * @class ModeConstraintsTest
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Tests\Validator
 * @version $Id$
 */
class ModeConstraintsTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Validator\ValidatorInterface', new ModeConstraints([]));
    }

    /**
     * @test
     * @dataProvider constraintsProvider
     */
    public function itShouldValidateConstraints($mode, $constraints, $parameters, $expected)
    {
        $validator = new ModeConstraints($constraints);

        $this->assertSame($expected, $validator->validate($mode, $parameters));
    }

    public function constraintsProvider()
    {
        return [
            [1, [1 => [200, 200]], [100, 100], true],
            [0, [0 => [200, 200]], [500, 500], true],
            [4, [4 => [200, 200]], [500, 100], false],
            [5, [5 => [100]], [50], true],
            [5, [5 => [100]], [200], false],
        ];
    }
}