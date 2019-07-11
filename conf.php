<?php

global $envProd, $uri_Start, $path, $orga;

//environnement production

$envProd = false;

//pour récupérer l'URL
$uri_Start = 2;
$path = "/Kiwiversity_events";

//school info
$orga = [
    "name" => "Alliance Française de Tucson",
    "logo_src" => "layout/images/logo_AFTucson.png",
    "website" => "www.aftucson.com",
    "address" => "2099E. River Road",
    "city" => "Tucson",
    "state" => "AZ",
    "zipcode" => "85718",
    "country" => "USA",
    "email" => "alliancefrancaisetucson@gmail.com",
    "phone" => "+1 520-881-9158",
    "events_website" => "",
    "admin_email" => "merialdo.gaelle@gmail.com"
];
