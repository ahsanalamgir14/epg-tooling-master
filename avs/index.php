<!DOCTYPE html>
<html lang="en">
<head>
    <title>AVS Dashboard
        <?php

        $host = substr(gethostname(), 0, 1);

        echo strtoupper($host);

        ?>
    </title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="10"/>
</head>
<body>
<div class="container-fluid">
    <h1>AVS Dashboard
        <?php

        if ($host == 'o') {
            echo 'DEVELOPMENT';
        } elseif ($host == 't') {
            echo 'TEST';
        } elseif ($host == 'a') {
            echo 'ACCEPTANCE';
        } elseif ($host == 'p') {
            echo 'PRODUCTION';
        }

        ?>
    </h1>
    <table class="table">
        <tr>
            <th>Correlation ID</th>
            <th>Start/end time</th>
            <th>Initiated by</th>
            <th>Playlists</th>
            <th></th>
            <th>Progress AVS 6.3</th>
            <th>Progress AVS 6.7</th>
        </tr>
        <?php
        require_once 'config.php';

        $mysql = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, "mdm");

        if (!$mysql->connect_errno) {
            $result = $mysql->query("SELECT platform, correlation_id, status, min(date_created) AS min, max(date_modified) AS max, count(*) AS count FROM mdm.playlist_status where platform in ('iTV AWS', 'iTV 6.7') GROUP BY platform, correlation_id, status ORDER BY date_created DESC, status");

            $runs = array();

            if (!$result) {
                echo "<p> Error: " . mysqli_error($mysql) . "</p>";
            } else {

                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                    $correlationId = $row["correlation_id"];
                    $platform = $row["platform"];
                    $min = $row["min"];
                    $max = $row["max"];

                    if (!array_key_exists($correlationId, $runs)) {
                        $runs[$correlationId] = array();
                        $runs[$correlationId]["min"] = $min;
                        $runs[$correlationId]["max"] = $max;
                    }

                    if ($min < $runs[$correlationId]["min"]) {
                        $runs[$correlationId]["min"] = $min;
                    }

                    if ($max > $runs[$correlationId]["max"]) {
                        $runs[$correlationId]["max"] = $max;
                    }

                    if (!array_key_exists($platform, $runs[$correlationId])) {
                        $runs[$correlationId][$platform] = array();
                        $runs[$correlationId][$platform]["ingestion"] = array();
                        $runs[$correlationId][$platform]["count"] = 0;
                    }

                    $status = $row["status"];
                    $count = $row["count"];

                    $runs[$correlationId][$platform]["count"] += $count;

                    if (!array_key_exists($status, $runs[$correlationId][$platform]["ingestion"])) {
                        $runs[$correlationId][$platform]["ingestion"][$status] = 0;
                    }
                    $runs[$correlationId][$platform]["ingestion"][$status] += $count;
                }

                $mysql->close();

                foreach ($runs as $runId => $platforms) {

                    $avs63Run = $platforms["iTV AWS"];
                    $avs67Run = $platforms["iTV 6.7"];
                    $count = $avs63Run["count"];

                    echo "<tr><td class=\"col-md-2\">";

                    echo '<a href="run.php?id=' . $runId . '">' . $runId . '</a>';

                    echo "</td><td class=\"col-md-1\">";

                    echo $platforms["min"];
                    echo "<br>";
                    echo $platforms["max"];

                    echo "</td><td class=\"col-md-1\">";

                    if (contains($runId, 'tapps864') ||
                        contains($runId, 'aapps11245') ||
                        contains($runId, 'aapps11246') ||
                        contains($runId, 'papps1880') ||
                        contains($runId, 'papps1882')) {
                        $started = "EPG run";
                    } elseif (contains($runId, 'tapps861') ||
                        contains($runId, 'aapps11256') ||
                        contains($runId, 'aapps11257') ||
                        contains($runId, 'papps1890') ||
                        contains($runId, 'papps1891')) {
                        $started = "Revoke";
                    } else if (preg_match('/^ID:[a-f0-9]{8}(-[a-f0-9]{4}){3}-[a-f0-9]{12}.+$/', $runId)) {
                        $started = "Artemis console";
                    } else {
                        $started = "Unknown";
                    }

                    echo $started;

                    echo "</td><td class=\"col-md-1\">";

                    echo $avs63Run["count"];

                    echo "</td><td class=\"col-md-1\">";

                    if (!array_key_exists("generated", $avs63Run["ingestion"])) {
                        $avs63Generated = 0;
                    } else {
                        $avs63Generated = ($avs63Run["ingestion"]["generated"] / $count) * 100;
                    }

                    if (!array_key_exists("generated", $avs67Run["ingestion"])) {
                        $avs67Generated = 0;
                    } else {
                        $avs67Generated = ($avs67Run["ingestion"]["generated"] / $count) * 100;
                    }

                    if (!array_key_exists("generation_failed", $avs63Run["ingestion"])) {
                        $avs63GenerationFailed = 0;
                    } else {
                        $avs63GenerationFailed = ($avs63Run["ingestion"]["generation_failed"] / $count) * 100;
                    }

                    if (!array_key_exists("generation_failed", $avs67Run["ingestion"])) {
                        $avs67GenerationFailed = 0;
                    } else {
                        $avs67GenerationFailed = ($avs67Run["ingestion"]["generation_failed"] / $count) * 100;
                    }

                    if (!array_key_exists("ingested", $avs63Run["ingestion"])) {
                        $avs63Ingested = 0;
                    } else {
                        $avs63Ingested = ($avs63Run["ingestion"]["ingested"] / $count) * 100;
                    }

                    if (!array_key_exists("ingested", $avs67Run["ingestion"])) {
                        $avs67Ingested = 0;
                    } else {
                        $avs67Ingested = ($avs67Run["ingestion"]["ingested"] / $count) * 100;
                    }

                    if (!array_key_exists("ingestion_failed", $avs63Run["ingestion"])) {
                        $avs63IngestionFailed = 0;
                    } else {
                        $avs63IngestionFailed = ($avs63Run["ingestion"]["ingestion_failed"] / $count) * 100;
                    }

                    if (!array_key_exists("ingestion_failed", $avs67Run["ingestion"])) {
                        $avs67IngestionFailed = 0;
                    } else {
                        $avs67IngestionFailed = ($avs67Run["ingestion"]["ingestion_failed"] / $count) * 100;
                    }

                    if (!array_key_exists("ready", $avs63Run["ingestion"])) {
                        $avs63Ready = 0;
                    } else {
                        $avs63Ready = ($avs63Run["ingestion"]["ready"] / $count) * 100;
                    }

                    if (!array_key_exists("ready", $avs67Run["ingestion"])) {
                        $avs67Ready = 0;
                    } else {
                        $avs67Ready = ($avs67Run["ingestion"]["ready"] / $count) * 100;
                    }

                    $avs63IngestFailed = $avs63GenerationFailed + $avs63IngestionFailed;
                    $avs63IngestedPercent = round($avs63Ingested, 2);
                    $avs63GeneratedPercent = round($avs63Generated, 2);
                    $avs63IngestFailedPercent = round($avs63IngestFailed, 2);
                    $avs63ReadyPercent = round($avs63Ready, 2);

                    $avs67IngestFailed = $avs67GenerationFailed + $avs67IngestionFailed;
                    $avs67IngestedPercent = round($avs67Ingested, 2);
                    $avs67GeneratedPercent = round($avs67Generated, 2);
                    $avs67IngestFailedPercent = round($avs67IngestFailed, 2);
                    $avs67ReadyPercent = round($avs67Ready, 2);

                    // For now AVS ingested, not ready
                    if ($avs63Ready == 100 && (!array_key_exists('iTV 6.7', $platforms) || $avs67Ready == 100)) {
                        echo "<img src=\"100.png\" style=\"width: 60px; height: 60px;\"/>";
                    } else if ($avs63IngestFailed > 0 || $avs67IngestFailed > 0) {
                        echo "<img src=\"warning.png\" style=\"width: 60px; height: 60px;\"/>";
                    }

                    echo "</td><td class=\"col-md-3\">";


                    echo "<div class=\"progress\" style=\"margin: 0; height: 60px;\">";

                    if ($avs63Ready > 0) {
                        echo "<div class=\"progress-bar progress-bar-success\" role=\"progressbar\" style=\"width:$avs63Ready%\">Ready $avs63ReadyPercent%</div>";
                    }

                    if ($avs63Ingested > 0) {
                        echo "<div class=\"progress-bar progress-bar-info\" role=\"progressbar\" style=\"width:$avs63Ingested%\">Ingested $avs63IngestedPercent%</div>";
                    }

                    if ($avs63Generated > 0) {
                        echo "<div class=\"progress-bar progress-bar-warning progress-bar-striped active\" role=\"progressbar\" style=\"width:$avs63Generated%\">Generated $avs63GeneratedPercent%</div>";
                    }

                    if ($avs63IngestFailed > 0) {
                        echo "<div class=\"progress-bar progress-bar-danger\" role=\"progressbar\" style=\"width:$avs63IngestFailed%\">Failed $avs63IngestFailedPercent%</div>";
                    }

                    echo "</div>";

                    echo "</td><td class=\"col-md-3\">";


                    echo "<div class=\"progress\" style=\"margin: 0; height: 60px;\">";

                    if ($avs67Ready > 0) {
                        echo "<div class=\"progress-bar progress-bar-success\" role=\"progressbar\" style=\"width:$avs67Ready%\">Ready $avs67ReadyPercent%</div>";
                    }

                    if ($avs67Ingested > 0) {
                        echo "<div class=\"progress-bar progress-bar-info\" role=\"progressbar\" style=\"width:$avs67Ingested%\">Ingested $avs67IngestedPercent%</div>";
                    }

                    if ($avs67Generated > 0) {
                        echo "<div class=\"progress-bar progress-bar-warning progress-bar-striped active\" role=\"progressbar\" style=\"width:$avs67Generated%\">Generated $avs67GeneratedPercent%</div>";
                    }

                    if ($avs67IngestFailed > 0) {
                        echo "<div class=\"progress-bar progress-bar-danger\" role=\"progressbar\" style=\"width:$avs67IngestFailed%\">Failed $avs67IngestFailedPercent%</div>";
                    }

                    echo "</div>";
                    echo "</td></tr>";
                }
            }

        } else {
            echo "<p> Horror: " . $mysql->connect_error . "</p>";
        }

        function contains($haystack, $needle)
        {
            return strpos($haystack, $needle) !== false;
        }

        ?>
    </table>
</div>
</body>
</html>