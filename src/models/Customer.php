<?php

class Customer 
{
    public function __construct(private string $id, private string $name, private string $level, private string $shippingZone, private string $currency) {

    }

    public function getName() : string {
        return $this->name;
    }

    public function getLevel() : string {
        return $this->level;
    }

    public function getShippingZone() : string {
        return $this->shippingZone;
    }

    public function getCurrency() : string {
        return $this->currency;
    }
}