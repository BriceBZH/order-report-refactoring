<?php

class Product
{
    public function __construct(private string $id, private string $name, private string $category, private float $price, private float $weight, private bool $taxable) {

    }

    public function getName() : string {
        return $this->name;
    }

    public function getCategory() : string {
        return $this->category;
    }

    public function getPrice() : float {
        return $this->price;
    }

    public function getWeight() : float {
        return $this->weight;
    }

    public function getTaxable() : bool {
        return $this->taxable;
    }

}