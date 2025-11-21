<?php

class ShippingZone
{
    public function __construct(private string $zone, private float $base, private float $perKg) {

    }

    public function getZone() : string {
        return $this->zone;
    }

    public function getBase() : float {
        return $this->type;
    }

    public function getPerKg() : float {
        return $this->perKg;
    }
}