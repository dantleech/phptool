<?php

namespace DTL\PhpTool\Mutator;

class Change
{
    private $type;
    private $change;

    public function __construct($type, $change)
    {
        $this->type = $type;
        $this->change = $change;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getChange()
    {
        return $this->change;
    }
}
