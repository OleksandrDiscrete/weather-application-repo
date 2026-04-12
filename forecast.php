<?php
class Forecast
{
    public static $cities = [
        "Київ",
        "Львів",
        "Одеса",
        "Харків",
        "Дніпро",
        "Запоріжжя",
        "Кривий Ріг",
        "Миколаїв",
        "Вінниця",
        "Херсон",
        "Полтава",
        "Чернігів",
        "Черкаси",
        "Суми",
        "Житомир",
        "Горлівка",
        "Маріуполь",
        "Луганськ",
        "Рівне",
        "Івано-Франківськ",
        "Тернопіль"
    ];
    public static $days = ["Понеділок", "Вівторок", "Середа", "Четвер", "П'ятниця", "Субота", "Неділя"];
    public static $weather_status_icons = [
        '<h3 class="weather__item-status weather__item-status--sunny mb-4"><i class="bi bi-sun-fill"></i> Сонячно</h3>',
        '<h3 class="weather__item-status weather__item-status--grey mb-4"><i class="bi bi-cloud-fill"></i> Хмарно</h3>',
        '<h3 class="weather__item-status weather__item-status--grey mb-4"><i class="bi bi-cloud-drizzle-fill"></i> Дощ</h3>'
    ];
    public static $COOKIE_LIFETIME = 60 * 60 * 24 * 30;
}