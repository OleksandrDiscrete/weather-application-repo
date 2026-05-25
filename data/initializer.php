<?php
namespace WeatherMaster\Data;

include_once "database.php";
include_once "../repositories/adminUserRepository.php";
include_once "../repositories/cityRepository.php";

use WeatherMaster\Repositories\AdminUserRepository;
use WeatherMaster\Repositories\CityRepository;

$database = new Database();

$cityRepo = new CityRepository($database);
$cityRepo->initTable();
$cityRepo->seed();

$adminUserRepo = new AdminUserRepository($database);
$adminUserRepo->initTable();
$adminUserRepo->seed();

echo "Successfully initialized the database and seeded the tables.";