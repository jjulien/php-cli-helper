<?php

namespace CLIHelper;

class Helper {

    /**
     * An array of argument definitions
     * @var Argument[]
     */
    protected $args = array();

    /**
     * Associative array returned get getopt
     *
     * @var array
     */
    protected $options;

    /**
     * @param Argument $arg
     */
    public function addArgument(Argument $arg) {
        $this->args[$arg->getName()] = $arg;
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
        return $this->args[$name];
    }

    /**
     * @return mixed
     */
    public function getOptions() {
        return $this->options;
    }

    public function getValue($name) {
        if (!array_key_exists($name, $this->getArgs())) {
            throw new ArgumentNotFoundException();
        }

        // Get argument
        $arg = $this->getArgs()[$name];
        // Check for boolean arguments
        if ($arg->getType() == Argument::TYPE_BOOLEAN) {
               if ( array_key_exists($arg->getShortArg(), $this->getOptions()) ||
                    array_key_exists($arg->getLongArg(), $this->getOptions()) ) {
                   return true;
               } else {
                   return false;
               }
        }

        // Check for value arguments
        if ($arg->getType() == Argument::TYPE_VALUE) {
            if (array_key_exists($arg->getShortArg(), $this->getOptions())) {
                return $this->getOptions()[$arg->getShortArg()];
            }
            if (array_key_exists($arg->getLongArg(), $this->getOptions())) {
                return $this->getOptions()[$arg->getLongArg()];
            }
        }
    }

    /**
     * Triggers parsing of all provided Argument objects.  This will enforce all rules set for parameters and generate
     * errors encountered during the parsing process.  After calling this method, the helper is ready to provide
     * values for all arguments that were passed on the command line
     */
    public function parse() {
        $this->options = $this->getOptParse();
    }

    /**
     * Parses the array of Argument objects, generates getopt parameters and returns the associative array from getopt
     *
     * @return array
     */
    protected function getOptParse() {
        $shortOpts = "";
        $longOpts = array();
        foreach ( $this->getArgs() as $key => $arg ) {
            if ($arg->getShortArg()) {
                $shortOpts .= $arg->getShortArg();
                if ($arg->getType() != Argument::TYPE_BOOLEAN) {
                    $shortOpts .= ":";
                }
            }
            if ($arg->getLongArg()) {
                $longOpt = $arg->getLongArg();
                if ($arg->getType() != Argument::TYPE_BOOLEAN) {
                    $longOpt .= ":";
                }
                $longOpts[] = $longOpt;
            }
        }
        return getopt($shortOpts, $longOpts);
    }
}
