<?php

class City
{
    public function __construct(
        public int $id = 0,
        public string $name = "",
        public float $positionX = 0,
        public float $positionY = 0
    ) {
    }

    public const TABLE_NAME = "city";
}