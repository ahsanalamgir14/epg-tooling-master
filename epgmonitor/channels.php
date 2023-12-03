<?php

require_once 'config.php';

$made = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);
//$grid = new mysqli('localhost', 'root', 'new-password');

//if ($made->connect_errno || $grid->connect_errno) {
if ($made->connect_errno) {
    echo "NO!";
    echo $made->connect_error;
} else {

    $made->set_charset("utf8");
    //$grid->set_charset("utf8");

    $channels = array();

    $madeChannels = $made->query("select channel_reference_number, channel_code, channel_name from madeepgconfig.channelconfig where receive_epg = 'Y' and active = 'Y';");

    while ($row = $madeChannels->fetch_array(MYSQLI_ASSOC)) {

        $channel = array();

        $channel["ref"] = $row["channel_reference_number"];
        $channel["code"] = $row["channel_code"];
        $channel["name"] = $row["channel_name"];

        $channels[] = $channel;
    }

    $made->close();

    for ($i = 0; $i < count($channels); $i++) {
        unset($channels[$i]["code"]);
    }

    //usort($channels, "compareChannel");

    header('Content-type: application/json');
    echo json_encode($channels);
}
