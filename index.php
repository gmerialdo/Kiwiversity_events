<?php

require_once "model/Model.php";
require_once "view/View.php";
require_once "conf.php";

//autoload for all controllers
function my_autoloader($class) {
    require_once("controller/".$class.".php");
}
spl_autoload_register('my_autoloader');

//implement with db data
$model = new Model("ocr-projet5-db", "root", "");

$session = new Session();

// show errors if not in envProd
global $envProd;
if (!$envProd){
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

//filters for all post inputs
$safeData = new Security([
    "post" => [
        "user_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "new_email" => FILTER_SANITIZE_EMAIL,
        "email" => FILTER_SANITIZE_EMAIL,
        "new_first_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "new_last_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "password" => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_LOW],
        "new_password" => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_LOW],
        "name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "description" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "category" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "active_event" => FILTER_SANITIZE_NUMBER_INT,
        "event_id" => FILTER_SANITIZE_NUMBER_INT,
        "location_id" => FILTER_SANITIZE_NUMBER_INT,
        "image_id" => FILTER_SANITIZE_NUMBER_INT,
        "type_tickets" => FILTER_SANITIZE_NUMBER_INT,
        "public" => FILTER_SANITIZE_NUMBER_INT,
        "members_only" => FILTER_SANITIZE_NUMBER_INT,
        "enable_booking" => FILTER_SANITIZE_NUMBER_INT,
        "max_tickets" => FILTER_SANITIZE_NUMBER_INT,
        "evt_account_id"=> FILTER_SANITIZE_NUMBER_INT,
        "nb_tickets_adult_mb" => FILTER_SANITIZE_NUMBER_INT,
        "nb_tickets_adult" => FILTER_SANITIZE_NUMBER_INT,
        "nb_tickets_child_mb" => FILTER_SANITIZE_NUMBER_INT,
        "nb_tickets_child" => FILTER_SANITIZE_NUMBER_INT,
        "nb_tickets_all" => FILTER_SANITIZE_NUMBER_INT,
        "nb_available_tickets" => FILTER_SANITIZE_NUMBER_INT,
        "price_adult_mb" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_adult" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_child_mb" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_child" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_adult_mb_booked" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_adult_booked" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_child_mb_booked" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "price_child_booked" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "donation" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "total_paid" => ['filter' => FILTER_SANITIZE_NUMBER_FLOAT, 'flags' => FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION],
        "payment_datetime" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "start_date" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "start_time" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "finish_date" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "finish_time" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "new_image_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "img_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_name" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_address" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_city" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_zipcode" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_state" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_country" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "location_phone" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW],
        "max_occupancy" => FILTER_SANITIZE_NUMBER_INT
    ],
    "get" => [
        "token" => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_STRIP_LOW]
    ]
]);

// decide if it's visitor mode or admin mode and create page
switch ($safeData->_url[0]){
    case 'admin':
        $page = new PageAdmin($safeData->_url);
        break;
    case 'logged':
        $page = new PageLoggedVisitor($safeData->_url);
        break;
    default:
        $page = new PageVisitor($safeData->_url);
        break;
}

// display page
echo $page->getHtmlPage();
