<?php

namespace CLIHelper;

class Argument {

    /**
     * Argument acts as an on/off boolean switch
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Argument requires a value to be provided
     */
    const TYPE_VALUE   = 'value';

    /**
     * The short/single character name for this argument
     * @var string
     */
    protected $shortArg;

    /**
     * The long name for the argument
     * @var string
     */
    protected $longArg;

    /**
     * The name describing this argument
     * @var string
     */
    protected $name;

    /**
     * The type of argument
     * @var string
     */
    protected $type = Argument::TYPE_VALUE;

    /**
     * Is this argument required
     * @var booelan
     */
    protected $required = false;

    /**
     * Help message for this argument
     * @var string
     */
    protected $help;

    /**
     * The default value for this argument
     * @var mixed
     */
    protected $default;

    /**
     * @return string
     */
    public function getShortArg()
    {
        return $this->shortArg;
    }

    /**
     * @param string $shortArg
     */
    public function setShortArg($shortArg)
    {
        $this->shortArg = $shortArg;
    }

    /**
     * @return string
     */
    public function getLongArg()
    {
        return $this->longArg;
    }

    /**
     * @param string $longArg
     */
    public function setLongArg($longArg)
    {
        $this->longArg = $longArg;
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
    public function getRequired()
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


}