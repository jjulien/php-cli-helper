<?php

namespace CLIHelper;

class Option {

    /**
     * Option acts as an on/off boolean switch
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Option requires a value to be provided
     */
    const TYPE_VALUE   = 'value';

    /**
     * The short/single character name for this option
     * @var string
     */
    protected $shortOpt;

    /**
     * The long name for the option
     * @var string
     */
    protected $longOpt;

    /**
     * The name describing this option
     * @var string
     */
    protected $name;

    /**
     * The type of option
     * @var string
     */
    protected $type = Option::TYPE_VALUE;

    /**
     * Is this option required
     * @var boolean
     */
    protected $required = false;

    /**
     * Help message for this option
     * @var string
     */
    protected $help;

    /**
     * The default value for this option
     * @var mixed
     */
    protected $default;

    /**
     * @return string
     */
    public function getShortOpt()
    {
        return $this->shortOpt;
    }

    /**
     * @param string $shortOpt
     * @return $this
     * @throws InvalidOptionException
     */
    public function setShortOpt($shortOpt)
    {
        $opt = preg_replace("/^-/", "", $shortOpt);
        if (preg_match("/^-/", $opt)) {
            throw new InvalidOptionException("Short options cannot have more than one leading dashes");
        } elseif (strlen($opt) > 1) {
            throw new InvalidOptionException("Short options can only be a single characters");
            // If the length of $opt is 0 then that means either a blank string or single dash were passed in
        } elseif (strlen($opt) == 0 || ! preg_match("/^[a-zA-Z]/", $opt)) {
            throw new InvalidOptionException("Short options must be a single alpha character");
        }
        $this->shortOpt = $opt;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongOpt()
    {
        return $this->longOpt;
    }

    /**
     * @param string $longOpt
     * @throws InvalidOptionException
     */
    public function setLongOpt($longOpt)
    {
        $opt = preg_replace("/^--/", "", $longOpt);
        if (preg_match("/^-/", $opt)) {
            throw new InvalidOptionException("Long options must have exactly two leading dashes");
        } elseif (strlen($opt) < 2) {
            throw new InvalidOptionException("long options must be at least 2 characters long");
        } elseif (!preg_match("/^[a-zA-Z]+-*.*/", $opt) ) {
            throw new InvalidOptionException("long options must be made of alpha characters or dashes, and have to begin with exactly 2 dashes");
        }
        $this->longOpt = $opt;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws InvalidOptionException
     */
    public function setType($type)
    {
        if ($type == self::TYPE_BOOLEAN && $this->required) {
            throw new InvalidOptionException("An option cannot be both required and of type BOOLEAN");
        }
        $this->type = $type;
    }

    /**
     * @return booelan
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param $required
     * @throws InvalidOptionException
     */
    public function setRequired($required)
    {
        if ($required && $this->type == self::TYPE_BOOLEAN) {
            throw new InvalidOptionException("An option cannot be both required and of type BOOLEAN");
        }
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isDual() {
        return ($this->getShortOpt() && $this->getLongOpt());
    }

    /**
     * Returns the value that should be displayed on the help summary line of the usage clause
     * Required options are wrapped in < > and optional in [ ].  Mutually exclusive options are
     * separated with a |.  Options that require a value also contain the word VALUE after them.
     *
     * Examples:
     *   <-r VALUE| --required VALUE>
     *   [-o | --optional]
     *   [--not-mutually-exclusive VALUE]
     *   [-n]
     *
     * @return string
     */
    public function getHelpSummaryLine() {
        if ($this->getType() == self::TYPE_VALUE) {
            $valueString = " VALUE";
        } else {
            $valueString = "";
        }

        if ($this->isRequired()) {
            $openGroup = "<";
            $closeGroup = ">";
        } else {
            $openGroup = "[";
            $closeGroup = "]";
        }
        $summary = $openGroup;
        if ($this->isDual()) {
            $summary .= "-" . $this->getShortOpt() . $valueString . " | --" . $this->getLongOpt() . $valueString;
        } else {
            if ($this->getShortOpt()) {
                $summary .= "-" . $this->getShortOpt() . $valueString;
            }
            else {
                $summary .= "--" . $this->getLongOpt() . $valueString;
            }
        }
        $summary .= $closeGroup;
        return $summary;
    }

    /**
     * @return string
     */
    public function getShortOptDisplay() {
        return "-" . $this->getShortOpt();
    }

    /**
     * @return string
     */
    public function getLongOptDisplay() {
        return "--" . $this->getLongOpt();
    }


}