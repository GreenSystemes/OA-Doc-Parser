<?php

namespace OADP;

class ArgParser {
    public const ARG_KEY_COMPOSER = "--composer";

    public const ARG_KEY_SWAGGER_HEADER = "--swagger-header";

    public const ARG_KEY_SWAGGER_OUTPUT = "--swagger-output";

    /**
     * Path of composer file. Full from / root
     */
    private ?string $composerFilePath;

    /**
     * Path of Swagger header path. Full from / root
     */
    private ?string $swaggerHeaderPath;

    /**
     * Path of output swagger path. Full from / root
     */
    private ?string $swaggerOutputPath;

    private array $psr4;

    private ?string $program;

    private function __construct() {
        $this->composerFilePath = null;
        $this->swaggerHeaderPath = null;
        $this->swaggerOutputPath = null;
        $this->program = null;
        $this->psr4 = array();
    }

    public function getComposerFilePath() : ?string {
        return $this->composerFilePath;
    }

    public function getSwaggerHeaderPath() : ?string {
        return $this->swaggerHeaderPath;
    }

    public function getSwaggerOutputPath() : ?string {
        return $this->swaggerOutputPath;
    }

    public function getPSR4() : array {
        return $this->psr4;
    }

    public function isArgsValid() : bool {
        return $this->getComposerFilePath() !== null && $this->getSwaggerHeaderPath() !== null && $this->getSwaggerOutputPath();
    }

    /**
     * Get string error
     * @return null if no error
     */
    public function hasError() : ?string {
        if( ! $this->isArgsValid() ) {
            return $this->getUsage( $this->program );
        }

        //composer.json is valid ?
        if( ! is_file($this->getComposerFilePath()) ) {
            return $this->getComposerFilePath() . " not found";
        }

        $myComposerFileContent = file_get_contents($this->getComposerFilePath());
        if( $myComposerFileContent === false ) {
            return "Can't read " . $this->getComposerFilePath();
        }

        $myComposeFileContentParsed = json_decode( $myComposerFileContent, true );
        if( $myComposeFileContentParsed === null ) {
            return "Can't parse JSON file " . $this->getComposerFilePath();
        }

        //Does PSR4 exists ?
        if( ! isset($myComposeFileContentParsed['autoload']['psr-4'] ) || ! is_array($myComposeFileContentParsed['autoload']['psr-4'])) {
            return "Can't find PSR4 definition in " . $this->getComposerFilePath();
        }
        $this->psr4 = $myComposeFileContentParsed['autoload']['psr-4'];

        //swagger-header.yml is valid ?
        if( ! is_file($this->getSwaggerHeaderPath()) ) {
            return $this->getSwaggerHeaderPath() . " not found";
        }

        $swaggerHeaderContent = file_get_contents($this->getSwaggerHeaderPath());
        if( $swaggerHeaderContent === false ) {
            return "Can't read " . $this->getSwaggerHeaderPath();
        }

        //output-swagger.yml is valid ?
        $swaggerOutput = $argv[3];
        if( file_put_contents($this->getSwaggerOutputPath(), '') === false ) {
            return 'Can\'t write in : ' . $this->getSwaggerOutputPath();
        }

        return $null;
    }

    /**
     * Get usage string
     * @param $args0 string Program argv[0]
     */
    public function getUsage( string $args0 ) : string {
        return 'Usage : ' . $args0 . " " . self::ARG_KEY_COMPOSER . " ./path/to/your/composer.json " . self::ARG_KEY_SWAGGER_HEADER . " ./path/to/your/swagger-header.yml " . self::ARG_KEY_SWAGGER_OUTPUT . " ./path/to/your/output-swagger.yml";
    }

    /**
     * Extract args from argv
     */
    public static function extractArgs( array $args ) : ArgParser {
        $myArgsCount = count( $args );
        $myArgParser = new ArgParser();
        if( $myArgsCount != 0 ) {
            $myArgParser->program = $args[0];
            for( $i = 1; $i < $myArgsCount; $i++ ) {
                if( $args[$i] === self::ARG_KEY_COMPOSER ) {
                    $i++;
                    if( $i < $myArgsCount ) {
                        $myArgParser->composerFilePath = $args[$i];
                    }
                }
                else if( $args[$i] === self::ARG_KEY_SWAGGER_HEADER ) {
                    $i++;
                    if( $i < $myArgsCount ) {
                        $myArgParser->swaggerHeaderPath = $args[$i];
                    }
                }
                else if( $args[$i] === self::ARG_KEY_SWAGGER_OUTPUT ) {
                    $i++;
                    if( $i < $myArgsCount ) {
                        $myArgParser->swaggerOutputPath = $args[$i];
                    }
                }
            }
        }
        return $myArgParser;
    }
}