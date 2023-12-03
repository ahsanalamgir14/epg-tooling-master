<!DOCTYPE html>
<html lang="en">
<head>
    <title>AVS Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
</head>
<body>

<?php
require_once 'config.php';

$mysql = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, "mdm");

$id = getParameter("id");


echo "<div class=\"container-fluid\">
    <h1>$id</h1>
    <table class=\"table\">
        <tr>
            <th>Filename</th>
            <th>Playlist ID</th>
            <th>Platform</th>
            <th>Status</th>
            <th>Created</th>
            <th>Modified</th>
        </tr>";


if (!$mysql->connect_errno) {
    $result = $mysql->query('select filename, playlist_id, platform, status, date_created, date_modified from playlist_status where correlation_id = "' . $id . '"');

    $playlists = array();


    if (!$result) {
        echo "<p> Error: " . mysqli_error($mysql) . "</p>";
    } else {

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $playlist = array();

            $playlist["filename"] = $row["filename"];
            $playlist["playlist_id"] = $row["playlist_id"];
            $playlist["platform"] = $row["platform"];
            $playlist["status"] = $row["status"];
            $playlist["date_created"] = $row["date_created"];
            $playlist["date_modified"] = $row["date_modified"];

            array_push($playlists, $playlist);

        }

        $mysql->close();

        foreach ($playlists as $playlist) {


            switch ($playlist["status"]) {
                case "generated":
                    $class = "warning";
                    break;
                case "ingested":
                    $class = "info";
                    break;
                case "ready":
                    $class = "success";
                    break;
                case "generation_failed":
                case "ingestion_failed":
                    $class = "danger";
                    break;
                default:
                    $class = "";
                    break;
            }

            echo '<tr class="' . $class . '"><td>';

            echo $playlist["filename"];

            echo "</td><td>";

            echo $playlist["playlist_id"];

            echo "</td><td>";

            echo $playlist["platform"];

            echo "</td><td>";

            echo $playlist["status"];

            echo "</td><td>";

            echo $playlist["date_created"];

            echo "</td><td>";

            echo $playlist["date_modified"];

            echo "</td></tr>";
        }
    }


} else {
    echo "<p> Horror: " . $mysql->connect_error . "</p>";
}

function getParameter($key)
{
    return isset($_GET[$key]) ? $_GET[$key] : '';
}

?>
</table>
</div>
</body>
</html>
