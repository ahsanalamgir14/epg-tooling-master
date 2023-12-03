<?php

require_once 'utils.php';
require_once 'epg_item.php';
require_once 'config.php';

const PROGRAM = 'prg';
const SCHEDULE = 'sch';

$source = getParameter("source");
$date = getParameter("date");

header('Content-type: application/json');

if (empty($date)) {
    error("Date can not be empty");
} else if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
    error("'$date' is not a valid date");
} else {
    switch ($source) {
        case MADE:
        case MDM:
            try {
                getMysqlData($source, $date);
            } catch (Exception $exception) {
                error($exception->getMessage());
            }
            break;
        default:
            error("Invalid source");
            break;
    }
}

function getMysqlData($source, $date)
{
    $mysql = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);

    if ($mysql->connect_errno) {
        error($mysql->connect_error);
    } else {

        $mysql->set_charset("utf8");

        $epgdata = array();
        $utc = new DateTime($date . " 00:00:00", new DateTimeZone(date_default_timezone_get()));

        $utc->setTimezone(new DateTimeZone("UTC"));
        $dateTime = $utc->format("Y-m-d H:i:s");

        $dateTime = mysqli_real_escape_string($mysql, $dateTime);

        if ($source == MADE) {
            $query = "select channelconfig.channel_reference_number as chref, epgdata.id, epgdata.publishedstarttime, epgdata.publishedendtime, epgdata.title, (cast(extractvalue(epgdata.programinformation, '//Restriction[@type=''record'']/@type') = 'record' as signed) + cast(extractvalue(epgdata.programinformation, '//Restriction[@type=''startover'']/@type') = 'startover' as signed) * 2 + cast(extractvalue(epgdata.programinformation, '//Restriction[@type=''cutv'']/@type') = 'cutv' as signed) * 4 + cast(extractvalue(epgdata.programinformation, '//Restriction[@type=''trickplay'']/@type') = 'trickplay' as signed) * 8) as restriction from madeepgconfig.channelconfig, madeepg.epgdata where channelconfig.channel_code = epgdata.serviceid and publishedendtime > '$dateTime' and publishedstarttime < '$dateTime' + INTERVAL 1 DAY order by epgdata.publishedendtime asc;";
        } else {
            $query = "SELECT channelreferencenumber AS chref, id, publishedstarttime, publishedendtime, title, (CAST(EXTRACTVALUE(programinformation, '//Restriction[@type=''record'']/@type') = 'record' AS SIGNED) + CAST(EXTRACTVALUE(programinformation, '//Restriction[@type=''startover'']/@type') = 'startover' AS SIGNED) * 2 + CAST(EXTRACTVALUE(programinformation, '//Restriction[@type=''cutv'']/@type') = 'cutv' AS SIGNED) * 4 + CAST(EXTRACTVALUE(programinformation, '//Restriction[@type=''trickplay'']/@type') = 'trickplay' AS SIGNED) * 8) AS restriction FROM mdm.epgdata_distribute WHERE publishedendtime > '$dateTime' AND publishedstarttime < '$dateTime' + INTERVAL 1 DAY ORDER BY CAST(channelreferencenumber AS signed) ASC, publishedendtime ASC;";
        }

        $result = $mysql->query($query);

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $epgitem = array();

            $epgitem["i"] = $row["id"];
            $epgitem["s"] = $row["publishedstarttime"];
            $epgitem["e"] = $row["publishedendtime"];
            $epgitem["t"] = $row["title"];
            $epgitem["r"] = $row["restriction"];

            $epgdata[$row["chref"]][] = $epgitem;
        }

        $mysql->close();

        echo json_encode($epgdata);
    }
}

function parseSchedule($schedule)
{
    $row = str_getcsv($schedule, "|");

    $channelReferenceNumber = $row[0];
    $programReferenceNumber = $row[1];
    $date = $row[2];
    $time = $row[3];
    $duration = $row[4];

    $start = parseTime($date, $time);
    $end = clone $start;
    $end->add(new DateInterval('PT' . $duration . 'M'));

    return new EpgItem($channelReferenceNumber, $programReferenceNumber, $start, $end, null, 0);
}

function parseProgram($program)
{
    $row = str_getcsv($program, "|");
    $programReferenceNumber = $row[0];

    $restriction = 0;

    if (array_key_exists(44, $row) && $row[44] == 'Y') {
        $restriction += 1;
    }

    if (array_key_exists(49, $row) && $row[49] == 'Y') {
        $restriction += 2;
    }

    if (array_key_exists(48, $row) && $row[48] == 'Y') {
        $restriction += 4;
    }

    if (array_key_exists(50, $row) && $row[50] == 'Y') {
        $restriction += 8;
    }

    $title = $row[1];
    return new EpgItem(null, $programReferenceNumber, null, null, $title, $restriction);
}

function parseTime($date, $time)
{
    return new DateTime(join(array(substr($date, 4, 4), substr($date, 0, 2), substr($date, 2, 2)), "-") . ' ' . join(array(substr($time, 0, 2), substr($time, 2, 2), "00"), ":"), new DateTimeZone("UTC"));
}

/**
 * @param $a EpgItem
 * @param $b EpgItem
 * @return int
 */
function compareProgram($a, $b)
{
    if ($a->getStart() > $b->getStart()) {
        return 1;
    } else if ($a->getStart() < $b->getStart()) {
        return -1;
    } else {
        return 0;
    }
}