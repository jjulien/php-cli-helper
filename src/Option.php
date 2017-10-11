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
     */
    public function setShortOpt($shortOpt)
    {
        $this->shortOpt = $shortOpt;
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
     */
    public function setLongOpt($longOpt)
    {
        $this->longOpt = $longOpt;
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
     */
    public function setType($type)
    {
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
     * @param booelan $required
     */
    public function setRequired($required)
    {
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isDual() {
        return ($this->getShortOpt() && $this->getLongOpt());
    }

    public function getHelpSummaryLine() {
        $summary = "";
        if ($this->isRequired()) {
            $openGroup = "<";
            $closeGroup = ">";
        } else {
            $openGroup = "[";
            $closeGroup = "]";
        }
        $summary = $openGroup;
        if ($this->isDual()) {
            $summary .= "-" . $this->getShortOpt() . " | --" . $this->getLongOpt();
        } else {
            if ($this->getShortOpt()) {
                $summary .= "-" . $this->getShortOpt();
            }
            else {
                $summary .= "--" . $this->getLongOpt();
            }
        }
        $summary .= $closeGroup;
        return $summary;
    }
}