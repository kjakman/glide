<?php

namespace League\Glide\Manipulators;

use Mockery;

class SizeTest extends \PHPUnit_Framework_TestCase
{
    private $manipulator;
    private $callback;

    public function setUp()
    {
        $this->manipulator = new Size();
        $this->callback = Mockery::on(function () {
            return true;
        });
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateInstance()
    {
        $this->assertInstanceOf('League\Glide\Manipulators\Size', $this->manipulator);
    }

    public function testSetMaxImageSize()
    {
        $this->manipulator->setMaxImageSize(500 * 500);
        $this->assertSame(500 * 500, $this->manipulator->getMaxImageSize());
    }

    public function testGetMaxImageSize()
    {
        $this->assertNull($this->manipulator->getMaxImageSize());
    }

    public function testRun()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('width')->andReturn('200')->twice();
            $mock->shouldReceive('height')->andReturn('200')->once();
            $mock->shouldReceive('resize')->with('100', '100', $this->callback)->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['w' => 100])->run($image)
        );
    }

    public function testGetWidth()
    {
        $this->assertSame(100.0, $this->manipulator->setParams(['w' => '100'])->getWidth());
        $this->assertSame(100.1, $this->manipulator->setParams(['w' => 100.1])->getWidth());
        $this->assertSame(null, $this->manipulator->setParams(['w' => null])->getWidth());
        $this->assertSame(null, $this->manipulator->setParams(['w' => 'a'])->getWidth());
        $this->assertSame(null, $this->manipulator->setParams(['w' => '-100'])->getWidth());
    }

    public function testGetHeight()
    {
        $this->assertSame(100.0, $this->manipulator->setParams(['h' => '100'])->getHeight());
        $this->assertSame(100.1, $this->manipulator->setParams(['h' => 100.1])->getHeight());
        $this->assertSame(null, $this->manipulator->setParams(['h' => null])->getHeight());
        $this->assertSame(null, $this->manipulator->setParams(['h' => 'a'])->getHeight());
        $this->assertSame(null, $this->manipulator->setParams(['h' => '-100'])->getHeight());
    }

    public function testGetFit()
    {
        $this->assertSame('contain', $this->manipulator->setParams(['fit' => 'contain'])->getFit());
        $this->assertSame('fill', $this->manipulator->setParams(['fit' => 'fill'])->getFit());
        $this->assertSame('max', $this->manipulator->setParams(['fit' => 'max'])->getFit());
        $this->assertSame('stretch', $this->manipulator->setParams(['fit' => 'stretch'])->getFit());
        $this->assertSame('crop', $this->manipulator->setParams(['fit' => 'crop'])->getFit());
        $this->assertSame('contain', $this->manipulator->setParams(['fit' => 'invalid'])->getFit());
    }

    public function testGetCrop()
    {
        $this->assertSame('center', $this->manipulator->setParams(['fit' => 'crop'])->getCrop());
        $this->assertSame('top-left', $this->manipulator->setParams(['fit' => 'crop-top-left'])->getCrop());
        $this->assertSame('top', $this->manipulator->setParams(['fit' => 'crop-top'])->getCrop());
        $this->assertSame('top-right', $this->manipulator->setParams(['fit' => 'crop-top-right'])->getCrop());
        $this->assertSame('left', $this->manipulator->setParams(['fit' => 'crop-left'])->getCrop());
        $this->assertSame('center', $this->manipulator->setParams(['fit' => 'crop-center'])->getCrop());
        $this->assertSame('right', $this->manipulator->setParams(['fit' => 'crop-right'])->getCrop());
        $this->assertSame('bottom-left', $this->manipulator->setParams(['fit' => 'crop-bottom-left'])->getCrop());
        $this->assertSame('bottom', $this->manipulator->setParams(['fit' => 'crop-bottom'])->getCrop());
        $this->assertSame('bottom-right', $this->manipulator->setParams(['fit' => 'crop-bottom-right'])->getCrop());
        $this->assertSame('center', $this->manipulator->setParams(['fit' => null])->getCrop());
        $this->assertSame('center', $this->manipulator->setParams(['fit' => 'invalid'])->getCrop());
    }

    public function testResolveMissingDimensions()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('width')->andReturn(400);
            $mock->shouldReceive('height')->andReturn(200);
        });

        $this->assertSame([400.0, 200.0], $this->manipulator->resolveMissingDimensions($image, false, false));
        $this->assertSame([100.0, 50.0], $this->manipulator->resolveMissingDimensions($image, 100, false));
        $this->assertSame([200.0, 100.0], $this->manipulator->resolveMissingDimensions($image, false, 100));
    }

    public function testLimitImageSize()
    {
        $this->assertSame([1000.0, 1000.0], $this->manipulator->limitImageSize(1000, 1000));
        $this->manipulator->setMaxImageSize(500 * 500);
        $this->assertSame([500.0, 500.0], $this->manipulator->limitImageSize(500, 500));
        $this->assertSame([500.0, 500.0], $this->manipulator->limitImageSize(1000, 1000));
    }

    public function testRunResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('resize')->with('100', '100', $this->callback)->andReturn($mock)->times(3);
            $mock->shouldReceive('resize')->with('100', '100')->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with('100', '100', 'center')->andReturn($mock)->once();
            $mock->shouldReceive('fit')->with('100', '100', $this->callback, 'center')->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runResize($image, 'contain', '100', '100')
        );

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runResize($image, 'fill', '100', '100')
        );

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runResize($image, 'max', '100', '100')
        );

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runResize($image, 'stretch', '100', '100')
        );

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runResize($image, 'crop', '100', '100', 'center')
        );
    }

    public function testRunContainResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('resize')->with('100', '100', $this->callback)->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runContainResize($image, '100', '100')
        );
    }

    public function testRunFillResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function($mock) {
            $mock->shouldReceive('resize')->with('100', '100', $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with('100', '100', 'center')->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runFillResize($image, '100', '100')
        );
    }

    public function testRunMaxResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('resize')->with('100', '100', $this->callback)->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runMaxResize($image, '100', '100')
        );
    }

    public function testRunStretchResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('resize')->with('100', '100')->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runStretchResize($image, '100', '100')
        );
    }

    public function testRunCropResize()
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock) {
            $mock->shouldReceive('fit')->with('100', '100', $this->callback, 'center')->andReturn($mock)->once();
        });

        $this->assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->runCropResize($image, '100', '100', 'center')
        );
    }
}