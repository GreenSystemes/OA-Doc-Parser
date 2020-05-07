<?php

use PHPUnit\Framework\TestCase;

final class DummyTest extends TestCase
{

    public function testOnePlusOneEqualOnePlusOne(): void
    {
        $this->assertEquals(
            1+1,
            1+1
        );
    }
}
