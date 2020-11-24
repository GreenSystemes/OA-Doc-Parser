<?php

namespace OADP;

class AutoloadReader
{
    private ?string $classLoaderFile;

    private $classLoader;

    private function __construct()
    {
        $this->classLoaderFile = null;
        $this->classLoader = null;
    }

    public function getClassLoaderFile() : ?string
    {
        return $this->classLoaderFile;
    }

    public function getClassLoader() //@phpstan-ignore-line
    {
        return $this->classLoader;
    }

    public static function load( ?string $autoloadPath = null ) : ?AutoloadReader
    {
        $myPaths = array(
            __DIR__ . '/../../vendor/autoload.php',
            __DIR__ . '/../vendor/autoload.php',
            __DIR__ . '/../../autoload.php',
            __DIR__ . '/../autoload.php',
            __DIR__ . '/vendor/autoload.php',
            __DIR__ . '/autoload.php'
        );

        if( $autoloadPath !== null ) {
            $myPaths[] = $autoloadPath;
        }

        //Autoload.php OK ?
        $myAutoloadReader = null;
        foreach ( $myPaths as $file) {
            if (is_file($file)) {
                $myAutoloadReader = new AutoloadReader();
                $myAutoloadReader->classLoaderFile = $file;
                break;
            }
        }

        return $myAutoloadReader;
    }

    public function hasError() : ?string
    {
        if ($this->getClassLoaderFile() === null) {
            return "Autoloader not found";
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getPsr4Classes(array $psr4) : array
    {
        //Find all PSR4 paths
        if ($this->getClassLoaderFile() === null) {
            return array();
        }

        $this->classLoader = require($this->classLoaderFile);
        if ($this->getClassLoader() === null) {
            return array();
        }

        $myAllNamespacesPsr4 = $this->getClassLoader()->getPrefixesPsr4();
        $myNamespacePsr4Paths = array_map(static function (string $namespaceName, string $namespaceDir) use ($myAllNamespacesPsr4): array {
            return $myAllNamespacesPsr4[$namespaceName];
        }, array_keys($psr4), array_values($psr4));
        
        //Merge all PSR4 paths
        //   From [ [path1], [path2, path3]]
        //   To   [ path1, path2, path3 ]
        $myNamespacePsr4Paths = (static function ($pathsInSubArray) : array {
            $myOut = array();
            foreach ($pathsInSubArray as $subArray) {
                $myOut = array_merge($myOut, $subArray);
            }
            return $myOut;
        })($myNamespacePsr4Paths);
        
        //Get all classes
        return array_filter($this->getClassLoader()->getClassMap(), static function (string $classPath) use ($myNamespacePsr4Paths) : bool {
            foreach ($myNamespacePsr4Paths as $path) {
                if (strpos($classPath, $path) !== false) {
                    return true;
                }
            }
            return false;
        });
    }
}
