<?php

class City
{
    public function __construct(
        public int $id = 0,
        public string $name = "",
        public float $position_x = 0,
        public float $position_y = 0
    ) {}

    public const TABLE_NAME = "city";
}
