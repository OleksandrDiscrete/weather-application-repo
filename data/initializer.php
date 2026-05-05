<?php
include_once "database.php";
include_once "../repositories/adminUserRepository.php";
include_once "../repositories/cityRepository.php";

$database = new Database();

$cityRepo = new CityRepository($database);
$cityRepo->init_table();
$cityRepo->seed();

$adminUserRepo = new AdminUserRepository($database);
$adminUserRepo->init_table();
$adminUserRepo->seed();

echo "Successfully initialized the database and seeded the tables.";