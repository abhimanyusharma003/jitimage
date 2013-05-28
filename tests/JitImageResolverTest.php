<?php

/**
 * This File is part of the tests package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Tests\JitImage;

use \Closure;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use Thapp\JitImage\JitImageResolver;
use Thapp\JitImage\JitResolveConfiguration;

/**
 * Class: JitImageResolverTest
 *
 * @uses PHPUnit_Framework_TestCase
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageResolverTest extends TestCase
{

    /**
     * @test
     * @dataProvider paramProvider
     */
    public function testResolveParameter($expected, $params, $source, $filter)
    {
        $args = func_get_args();
        array_shift($args);

        $resolver = new JitImageResolver(new JitResolveConfiguration, $image = $this->getImageMock(), $this->getCacheMock());
        $this->setParamsWithoutResolving($resolver, $params, $source, $filter);

        $this->assertSame($expected, $this->getPropertyValue('parameter', $resolver));
    }

    /**
     * @test
     * @dataProvider paramProvider
     */
    public function testGetParameterValue($expected, $params, $source, $filter)
    {
        $args = func_get_args();
        array_shift($args);

        $resolver = new JitImageResolver(new JitResolveConfiguration, $image = $this->getImageMock(), $this->getCacheMock());
        $this->setParamsWithoutResolving($resolver, $params, $source, $filter);

        $params = $this->getPropertyValue('parameter', $resolver);

        $this->assertSame($expected['width'],  $params['width']);
        $this->assertSame($expected['source'], $params['source']);
        $this->assertSame($expected['filter'], $params['filter']);
    }

    /**
     * testResolveCachedImage
     *
     * @access public
     * @return mixed
     */
    public function testResolveImage()
    {
        $resolver = new JitImageResolver(new JitResolveConfiguration, $image = $this->getImageMock(function (&$mock) {
            $mock->shouldReceive('load')->andReturn(true);
            $mock->shouldReceive('process');
            $mock->shouldReceive('getContents')->andReturn('foo');
            $mock->shouldReceive('close');
        }), $this->getCacheMock(function (&$mock) use ($image) {
            $mock->shouldReceive('put');
            $mock->shouldReceive('has')->andReturn(false);
        }));

        $resolver->setResolveBase();
        $resolver->disableCache();

        $resolver->setParameter('0');
        $resolver->setSource('image.jpg');
        $resolver->setFilter(null);


        $this->assertFalse($resolver->resolve());

        $resolver->close();

        $resolver->setParameter('0');
        $resolver->setSource('http://example.com/image.jpg');
        $resolver->setFilter(null);

        $this->assertSame($image, $resolver->resolve());
    }
    /**
     * @test
     */
    public function testResolveCachedImage()
    {
        $resolver = new JitImageResolver(new JitResolveConfiguration(['cache' => true]), $image = $this->getImageMock(), $cache = $this->getCacheMock(function (&$mock) use ($image) {
            $mock->shouldReceive('has')->andReturn(true);
            $mock->shouldReceive('get')->andReturn($image);
            $mock->shouldReceive('getIdFromUrl')->andReturn('some/image.jpg');
        }));

        $resolver->setParameter('0');
        $resolver->setSource('some/image.jpg');
        $resolver->setFilter(null);

        $this->assertSame($image, $resolver->resolve());
    }

    /**
     * setParamsWithoutResolving
     *
     * @access private
     * @return mixed
     */
    private function setParamsWithoutResolving(&$resolver, $params, $source, $filter)
    {
        $resolver->setParameter($params);
        $resolver->setSource($source);
        $resolver->setFilter($filter);

        $this->invokeMethod('parseParameter', $resolver);
        $this->invokeMethod('parseSource', $resolver);
        $this->invokeMethod('parseFilter', $resolver);
    }

    /**
     * paramProvider
     */
    public function paramProvider()
    {
        return [
            [
                'params' => ['mode' => 0, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null,
                'source' => '/path/image.jpg',
                'filter' => []
            ],
            '0', '/path/image.jpg', null
            ],

            [
                'params' => ['mode' => 0, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null,
                'source' => '/path/image.jpg',
                'filter' => ['gs' => ['s' => '100']]
            ],
            '0', '/path/image.jpg', 'filter:gs;s=100'
            ],

            [
                'params' => ['mode' => 1, 'width' => 200, 'height' => 0, 'gravity' => null, 'background' => null,
                'source' => '/path/image.jpg',
                'filter' => []
            ],
            '1/200', '/path/image.jpg', null
            ],

            [
                'params' => ['mode' => 2, 'width' => 200, 'height' => 0, 'gravity' => null, 'background' => null,
                'source' => '/path/image.jpg',
                'filter' => []
            ],
            '2/200', '/path/image.jpg', null
            ],
        ];
    }



    protected function getImageMock(Closure $setup = null)
    {
        $image = m::mock('\Thapp\JitImage\ImageInterface');
        $image->shouldReceive('close');

        if (!is_null($setup)) {
            $setup($image);
        }

        return $image;

    }

    protected function getCacheMock(Closure $setup = null)
    {
        $cache = m::mock('\Thapp\JitImage\Cache\CacheInterface');

        $cache->shouldReceive('createKey');

        if (!is_null($setup)) {
            $setup($cache);
        }

        return $cache;
    }


}
