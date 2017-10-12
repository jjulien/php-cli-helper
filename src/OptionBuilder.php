<?php

namespace CLIHelper;


class OptionBuilder {

    /**
     * @var Option $option
     */
    private $option;

    /**
     * @var Helper $helper
     */
    private $helper;

    public function __construct(Helper $helper) {
        $this->option = new Option();
        $this->helper = $helper;
    }

    public function withName($name) {
        $this->option->setName($name);
        return $this;
    }
    /**
     * @param $short_opt
     * @return $this
     */
    public function withShort($short_opt) {
        $this->option->setShortOpt($short_opt);
        return $this;
    }

    public function withLong($long_opt) {
        $this->option->setLongOpt($long_opt);
        return $this;
    }

    public function withType($type) {
        $this->option->setType($type);
        return $this;
    }

    public function required() {
        $this->option->setRequired(true);
        return $this;
    }

    public function withHelp($help) {
        $this->option->setHelp($help);
        return $this;
    }

    public function boolean() {
        $this->option->setType(Option::TYPE_BOOLEAN);
        return $this;
    }

    public function build() {
        $this->helper->addOption($this->option);
    }

    public function done() {
        $this->build();
    }

}