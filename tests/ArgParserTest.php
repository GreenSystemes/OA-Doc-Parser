<?php

use PHPUnit\Framework\TestCase;
use OADP\ArgParser;

class ArgParserTest extends TestCase
{
    public function testBasic(): void
    {
        $myArgParser = ArgParser::testBuilder();

        self::assertNull( $myArgParser->getComposerFilePath() );
        self::assertNull( $myArgParser->getConfFilePath() );
        self::assertNull( $myArgParser->getSwaggerHeaderPath() );
        self::assertNull( $myArgParser->getSwaggerOutputPath() );
        self::assertEmpty( $myArgParser->getPSR4() );
        self::assertEmpty( $myArgParser->getTags() );
        self::assertFalse( $myArgParser->isArgsValid() );
    }
}