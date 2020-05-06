<?php
namespace OADP;

include_once('bootstrap.php');

use \ReflectionClass;

/**
 * Class to help annotations parsing
 */
class ClassParser {
    public const ANNOTATION_NAME = '@OA-Name';

    public const ANNOTATION_COMPONENT_BEGIN = '@OA-Component-Begin';
    public const ANNOTATION_COMPONENT_END = '@OA-Component-End';

    public const ANNOTATION_PROPERTY_BEGIN = '@OA-Property-Begin';
    public const ANNOTATION_PROPERTY_END = '@OA-Property-End';

    public const ANNOTATION_METHOD = '@OA-Method';

    public const ANNOTATION_PATH = '@OA-Path';

    public const ANNOTATION_PATH_BEGIN = '@OA-Path-Begin';
    public const ANNOTATION_PATH_END = '@OA-Path-End';

    /**
     * @var Component[]
     */
    private array $components;

    private array $routes;

    private function __construct() {
        $this->components = array();
        $this->routes = array();
    }

    /**
     * Read annotations from a class
     */
    private function testContainOpenApiAnnotation( ReflectionClass $reflectionClass ) : bool {
        
        //Case of component
        if( $reflectionClass->getDocComment() !== false && 
            ( $myName = self::extractValueAnnotation( self::ANNOTATION_NAME, $reflectionClass->getDocComment() ) ) !== null
        ) {
            $myComponent = new Component();
            $myComponent->setName( $myName );
            $myDescription = self::extractBlockAnnotation( self::ANNOTATION_COMPONENT_BEGIN, self::ANNOTATION_COMPONENT_END, $reflectionClass->getDocComment() );
            if( $myDescription !== null ) {
                $myComponent->setComponentDetail( $myDescription );

                foreach( $reflectionClass->getProperties() as $property ) {
                    if( $property->getDocComment() !== false ) {
                        $myPropertyDescription = self::extractBlockAnnotation( self::ANNOTATION_PROPERTY_BEGIN, self::ANNOTATION_PROPERTY_END, $property->getDocComment() );
                        if( $myPropertyDescription !== null ) {
                            $myComponent->addComponentProperty( $myPropertyDescription );
                        }
                    }
                }
            
                if( isset( $this->components[ $myComponent->getName() ] ) ) {
                    echo 'Component ' . $myComponent->getName() . ' describe more than one time !!!';
                    return false;
                }
                $this->components[ $myComponent->getName() ] = $myComponent;
            }
        }
        //Case of controller
        else {
            foreach( $reflectionClass->getMethods() as $classMethod ) {
                if( $classMethod->getDocComment() !== false && 
                    ( $myMethod = self::extractValueAnnotation( self::ANNOTATION_METHOD, $classMethod->getDocComment() ) ) !== null &&
                    in_array( $myMethod, array('GET', 'POST', 'DELETE', 'PUT', 'PATCH', 'HEAD', 'OPTIONS', 'TRACE') ) &&
                    ( $myPath = self::extractValueAnnotation( self::ANNOTATION_PATH, $classMethod->getDocComment() ) ) !== null &&
                    ( $myPathDetail = self::extractBlockAnnotation( self::ANNOTATION_PATH_BEGIN, self::ANNOTATION_PATH_END, $classMethod->getDocComment() ) ) !== null
                ) {
                    if( ! isset( $this->routes[ $myPath ] ) ) {
                        $this->routes[ $myPath ] = array();
                    }

                    if( ! isset( $this->routes[ $myPath ][ $myMethod ] ) ) {
                        $this->routes[ $myPath ][ $myMethod ] = $myPathDetail;
                    }
                    else {
                        echo 'Route [' . $myMethod . ']' . $myPath . ' describe more than one time !!!';
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Loop to extract data from classes
     */
    public function extractData( array $classes ) : ClassParser {
        $myClass = new ClassParser();
        $myTotalClasses = count( $classes );
        foreach( $classes as $classeName => $file ) {
            include_once($file);
            try {
                $myReflectionClass = new ReflectionClass( $classeName );
                echo "[" . $i. "/" . $myTotalClasses . "] " . $myReflectionClass->getName() . "\n";
                $i++;
                if( ! $myClass->testContainOpenApiAnnotation( $myReflectionClass ) ) {
                    echo "ERROR !!!\n";
                }
            }
            catch( Exception $e ) {
                echo $e->getMessage() . "\n";
            }
        }

        echo "\nExtractor found : \n";
        echo " - " . count( $myClass->components ) . " component(s)\n";
        echo " - " . count( $myClass->routes ) . " route(s)\n";

        return $myClass;
    }

    /**
     * Extract annotation from comment block as line. Take right part of annotation
     */
    private static function extractValueAnnotation( string $annotation, string $toAnalyze ) : ?string {
        foreach( explode(PHP_EOL, $toAnalyze) as $line ) {
            $myPos = strpos( $line, $annotation );
            if( $myPos !== false ) {
                $mySubstr = substr( $line, $myPos + strlen($annotation) + 1 );
                return ( $mySubstr !== '' ) ? $mySubstr : null;
            }
        }
        return null;
    }

    /**
     * Extract annotation from comment block as block. Take part between annotation (without annotation)
     */
    private static function extractBlockAnnotation( string $beginAnnotation, string $endAnnotation, string $toAnalyze ) : ?string {
        $myBegin = null;
        $myContent = array();
        foreach( explode(PHP_EOL, $toAnalyze) as $line ) {
            if( $myBegin === null ) {
                if( ($myPos = strpos( $line, $beginAnnotation ) ) !== false ) {
                    $myBegin = $myPos;
                }
            }
            else {
                if( strpos( $line, $endAnnotation ) === false ) {
                    $myContent[] = ( ( $beginAnnotation === self::ANNOTATION_PROPERTY_BEGIN ) ? '        ' : '      ' ) . substr( $line, strpos( $line, "*" ) + 2 );
                }
                else {
                    return implode( PHP_EOL, $myContent );
                }
            }
        }
        return null;
    }

    /**
     * From all data, generate swagger file
     * @param $argParser ArgParser Argument parser valid
     */
    public function generateSwagger( ArgParser $argParser ) : bool {
        if( $argParser->hasError() !== null ) {
            return false;
        }

        $myContent = file_get_contents( $argParser->getSwaggerHeaderPath() ) . PHP_EOL;
        $myContent .= 'components:' . PHP_EOL . '  schemas:' . PHP_EOL . implode( PHP_EOL, array_map( static function( Component $component ) : string {
            return (string)$component;
        }, $this->components ) ) . PHP_EOL;
        $myContent .= 'paths:' . PHP_EOL;
        foreach( $this->routes as $path => $content ) {
            $myContent .= '  ' . $path . PHP_EOL;
            foreach( $content as $method => $detail ) {
                $myContent .= '    ' . strtolower($method) . ':' . PHP_EOL;
                $myContent .= $detail . PHP_EOL;
            }
        }

        return file_put_contents( $argParser->getSwaggerOutputPath(), $myContent ) !== false;
    }
}