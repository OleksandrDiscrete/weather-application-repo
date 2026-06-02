<?php

namespace WeatherMaster\Models;

class VisitLog
{
    public function __construct(
        public int $id = 0,
        public string $page = "",
        public string $ipAddress = "",
        public string $userAgent = "",
        public string $visitedAt = ""
    ) {
    }
    /**
     * The name of the model in the database
     * @var string
     */
    public const TABLE_NAME = "visit_log";
}