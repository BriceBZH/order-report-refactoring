<?php

class Promotion 
{
    public function __construct(private string $code, private string $type, private int $value, private bool $active) {

    }

    public function getCode() : string {
        return $this->code;
    }

    public function getType() : string {
        return $this->type;
    }

    public function getValue() : int {
        return $this->value;
    }

    public function getActive() : bool {
        return $this->active;
    }
}