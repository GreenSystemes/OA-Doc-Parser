<?php

namespace OADP;

class ArgParser
{
    public const ARG_KEY_CONF = "--conf";

    public const ARG_KEY_TAG = "--tag";

    public const ARG_KEY_COMPOSER = "--composer";

    public const ARG_KEY_AUTOLOAD = "--autoload";

    public const ARG_KEY_SWAGGER_HEADER = "--swagger-header";

    public const ARG_KEY_SWAGGER_OUTPUT = "--swagger-output";

    public static function isDefinedArgs(string $args) : bool
    {
        return in_array($args, array( self::ARG_KEY_AUTOLOAD, self::ARG_KEY_CONF, self::ARG_KEY_TAG, self::ARG_KEY_COMPOSER, self::ARG_KEY_SWAGGER_HEADER, self::ARG_KEY_SWAGGER_OUTPUT ));
    }

    /**
     * Path of configuration file file. Full from / root
     */
    private ?string $confFilePath;

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

    /**
     * Path of output swagger path. Full from / root
     */
    private ?string $autoloadPath;

    /**
     * Tags required for parsing
     */
    private array $tags;

    private array $psr4;

    private ?string $program;

    private function __construct()
    {
        $this->composerFilePath = null;
        $this->swaggerHeaderPath = null;
        $this->swaggerOutputPath = null;
        $this->confFilePath = null;
        $this->autoloadPath = null;
        $this->program = null;
        $this->psr4 = array();
        $this->tags = array();
    }

    public function getComposerFilePath() : ?string
    {
        return $this->composerFilePath;
    }

    public function getConfFilePath() : ?string
    {
        return $this->confFilePath;
    }

    public function getSwaggerHeaderPath() : ?string
    {
        return $this->swaggerHeaderPath;
    }

    public function getSwaggerOutputPath() : ?string
    {
        return $this->swaggerOutputPath;
    }

    public function getAutoloadPath() : ?string
    {
        return $this->autoloadPath;
    }


    public function getPSR4() : array
    {
        return $this->psr4;
    }

    public function getTags() : array
    {
        return $this->tags;
    }

    public function isArgsValid() : bool
    {
        return $this->getComposerFilePath() !== null && $this->getSwaggerHeaderPath() !== null && $this->getSwaggerOutputPath() !== null;
    }

    /**
     * Get string error
     * @return null if no error
     */
    public function hasError() : ?string
    {
        if (! $this->isArgsValid()) {
            if ($this->getConfFilePath() === null) {
                return $this->getUsage($this->program);
            }

            //OA_Doc_Parser.json is valid ?
            if (! is_file($this->getConfFilePath())) {
                return $this->getConfFilePath() . " not found";
            }
    
            $myConfFileContent = file_get_contents($this->getConfFilePath());
            if ($myConfFileContent === false) {
                return "Can't read " . $this->getConfFilePath();
            }

            $myConfFileContent = json_decode($myConfFileContent, true);
            if ($myConfFileContent === null) {
                return "Can't parse JSON " . $this->getConfFilePath();
            }

            if (
                ! isset($myConfFileContent['composer'], $myConfFileContent['swagger']['header'], $myConfFileContent['swagger']['output']) ||
                ! is_string($myConfFileContent['composer']) || ! is_string($myConfFileContent['swagger']['header']) || ! is_string($myConfFileContent['swagger']['output'])
            ) {
                return "Can't parse args defined in JSON " . $this->getConfFilePath();
            }

            if (isset($myConfFileContent['swagger']['tags']) && ! is_array($myConfFileContent['swagger']['tags'])) {
                return "swagger.tags defined in JSON is not an array in " . $this->getConfFilePath();
            }

            $this->composerFilePath = $myConfFileContent['composer'];
            $this->swaggerHeaderPath = $myConfFileContent['swagger']['header'];
            $this->swaggerOutputPath = $myConfFileContent['swagger']['output'];
            if (isset($myConfFileContent['swagger']['tags'])) {
                $this->tags = $myConfFileContent['swagger']['tags'];
            }

            if( isset($myConfFileContent['autoload']) && is_string($myConfFileContent['autoload']) ) {
                $this->autoloadPath = $myConfFileContent['autoload'];
            }
        }

        //composer.json is valid ?
        if (! is_file($this->getComposerFilePath())) {
            return $this->getComposerFilePath() . " not found";
        }

        $myComposerFileContent = file_get_contents($this->getComposerFilePath());
        if ($myComposerFileContent === false) {
            return "Can't read " . $this->getComposerFilePath();
        }

        $myComposeFileContentParsed = json_decode($myComposerFileContent, true);
        if ($myComposeFileContentParsed === null) {
            return "Can't parse JSON file " . $this->getComposerFilePath();
        }

        //Does PSR4 exists ?
        if (! isset($myComposeFileContentParsed['autoload']['psr-4']) || ! is_array($myComposeFileContentParsed['autoload']['psr-4'])) {
            return "Can't find PSR4 definition in " . $this->getComposerFilePath();
        }
        $this->psr4 = $myComposeFileContentParsed['autoload']['psr-4'];

        //swagger-header.yml is valid ?
        if (! is_file($this->getSwaggerHeaderPath())) {
            return $this->getSwaggerHeaderPath() . " not found";
        }

        $mySwaggerHeaderContent = file_get_contents($this->getSwaggerHeaderPath());
        if ($mySwaggerHeaderContent === false) {
            return "Can't read " . $this->getSwaggerHeaderPath();
        }

        //output-swagger.yml is valid ?
        if (file_put_contents($this->getSwaggerOutputPath(), '') === false) {
            return 'Can\'t write in : ' . $this->getSwaggerOutputPath();
        }

        if( $this->getAutoloadPath() !== null && ! is_file( $this->getAutoloadPath() ) ) {
            return 'Can\'t read autoload in : ' . $this->getAutoloadPath();
        }

        return null;
    }

    /**
     * Get usage string
     * @param string $args0 Program argv[0]
     */
    public function getUsage(string $args0) : string
    {
        return "Usage : \n" . $args0 . " " . self::ARG_KEY_COMPOSER . " ./path/to/your/composer.json " . self::ARG_KEY_SWAGGER_HEADER . " ./path/to/your/swagger-header.yml " . self::ARG_KEY_SWAGGER_OUTPUT . " ./path/to/your/output-swagger.yml " . self::ARG_KEY_AUTOLOAD . " ./path/to/your/autoload.php\n" . $args0 . " " . self::ARG_KEY_CONF . " ./path/to/your/OA_Doc_Parser.json";
    }

    /**
     * Extract args from argv
     */
    public static function extractArgs(array $args) : ArgParser
    {
        $myArgsCount = count($args);
        $myArgParser = new ArgParser();
        if ($myArgsCount != 0) {
            $myArgParser->program = $args[0];
            for ($i = 1; $i < $myArgsCount; $i++) {
                if ($args[$i] === self::ARG_KEY_COMPOSER) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->composerFilePath = $args[$i];
                    }
                } elseif ($args[$i] === self::ARG_KEY_SWAGGER_HEADER) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->swaggerHeaderPath = $args[$i];
                    }
                } elseif ($args[$i] === self::ARG_KEY_SWAGGER_OUTPUT) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->swaggerOutputPath = $args[$i];
                    }
                } elseif ($args[$i] === self::ARG_KEY_CONF) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->confFilePath = $args[$i];
                    }
                } elseif ($args[$i] === self::ARG_KEY_AUTOLOAD) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->autoloadPath = $args[$i];
                    }
                }elseif ($args[$i] === self::ARG_KEY_TAG) {
                    $i++;
                    if ($i < $myArgsCount && ! self::isDefinedArgs($args[$i])) {
                        $myArgParser->tags[] = $args[$i];
                    }
                }
            }
        }
        return $myArgParser;
    }

    public static function testBuilder() : ArgParser
    {
        return new ArgParser();
    }
}
