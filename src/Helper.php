<?php

namespace CLIHelper;


class Helper {

    /**
     * An array of argument definitions
     * @var Argument[]
     */
    protected $args = array();

    /**
     * @param Argument $arg
     */
    public function addArgument(Argument $arg) {
        $this->args[] = $arg;
    }

    /**
     * @return Argument[]
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param Argument[] $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * @param string $name
     * @return Argument
     */
    public function getArg($name) {
        foreach ( $this->getArgs() as $arg ) {
            if ( $arg->getName() == $name ) {
                return $arg;
            }
        }
    }
}
