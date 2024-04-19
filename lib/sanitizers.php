<?php

function sanitize_email($email = "")
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
function is_valid_email($email = "")
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}
function is_valid_username($username)
{
    return preg_match('/^[a-z0-9_-]{3,16}$/', $username);
}
function is_valid_password($password)
{
    return strlen($password) >= 8;
}



function is_valid_apiID($apiID)
{
    return preg_match('/^[a-zA-Z0-9]{6,20}$/', $apiID);
}
function is_valid_productName($name)
{
    return preg_match('/^[^\d]{1,32}$/', $name);
}
function is_valid_price($price)
{
    return preg_match('/^(?:\d{1,7}|\d{1,5}\.\d{1,2})$/', $price);
}
function is_valid_measurement($measurement)
{
    return preg_match('/^[^\\"\'@#$%^*()?<>~`|+\-,_=;:;\[\]{}]{1,75}$/', $measurement);
}
function is_valid_productType($type)
{
    return preg_match('/^.{1,100}$/', $type);
}
function is_valid_url($url)
{
    return preg_match('/(https?:\/\/)?(www\.)[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', $url);
}
function is_valid_category($category)
{
    return preg_match('/^[\w\s&]{1,100}$/', $category);
}
function is_valid_stock($stock)
{
    return preg_match('/^\d+$/', $stock);
}