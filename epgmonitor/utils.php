<?php

const MADE = "made";
const MDM = "mdm";

function getParameter($key)
{
    return isset($_GET[$key]) ? $_GET[$key] : '';
}

function error($message)
{
    $response = array();
    $response["error"] = $message;
    header(' ', true, 400);
    echo json_encode($response);
}