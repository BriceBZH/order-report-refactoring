<?php

class Order
{
    public function __construct(private string $id, private string $customerId, private string $productId, private int $qty, private float $unitPrice, private string $date, private ?string $promoCode, private string $time) {

    }

    public function getName() : string {
        return $this->name;
    }

    public function getCustomerId() : string {
        return $this->customerId;
    }

    public function getProductId() : string {
        return $this->productId;
    }

    public function getQty() : int {
        return $this->qty;
    }

    public function getUnitPrice() : float {
        return $this->unitPrice;
    }

    public function getWeight() : float {
        return $this->weight;
    }

    public function getDate() : bool {
        return $this->date;
    }

    public function getPromoCode() : ?string {
        return $this->promoCode;
    }

    public function getTime() : string {
        return $this->time;
    }
}