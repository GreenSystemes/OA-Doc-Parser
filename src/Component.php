<?php

namespace OADP;

class Component {
    /**
     * Name extract from @OA-Name
     */
    private string $name;

    /**
     * Component detail
     */
    private string $componentDetail;

    /**
     * All properties between @OA-Property-Begin and @OA-Property-End
     * @var string[]
     */
    private array $componentProperties;

    public function __construct() {
        $this->name = '';
        $this->componentDetail = '';
        $this->componentProperties = array();
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;
        return $this;
    }

    public function getComponentDetail() : string {
        return $this->componentDetail;
    }

    public function setComponentDetail(string $componentDetail) : self {
        $this->componentDetail = $componentDetail;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getComponentProperties() : array {
        return $this->name;
    }

    public function addComponentProperty(string $componentProperty) : self {
        $this->componentProperties[] = $componentProperty;
        return $this;
    }

    /**
     * @param $componentProperties string[]
     */
    public function setComponentProperties(array $componentProperties) : self {
        $this->componentProperties = $componentProperties;
        return $this;
    }

    public function isValid() : bool {
        return $this->getName() === '' || $this->getComponentDetail() === '';
    }

    /**
     * Generate YAML from object data
     */
    public function __toString() : string {
        $myOut = '    ' . $this->getName() . ':' . PHP_EOL . $this->getComponentDetail() . PHP_EOL;

        if( ! empty( $this->componentProperties ) ) {
            $myOut .= '      properties:' . PHP_EOL . implode( PHP_EOL, $this->componentProperties );
        }

        return $myOut;
    }
}