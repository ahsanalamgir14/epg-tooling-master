<!DOCTYPE html>
<html lang="en">
<head>
    <title>MediaConvert Jobs</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="10"/>
</head>
<body>

<?php
require_once 'config.php';

$mysql = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, "made");

echo "<div class=\"container-fluid\">
    <h1>MediaConvert Jobs</h1>
    <table class=\"table\">
        <tr>
            <th>ID</th>
            <th>Asset ID</th>
            <th>Title</th>
            <th>Resolution</th>
            <th>Type</th>
            <th>Profile</th>
            <th>Status</th>
            <th>Percentage</th>
            <th>Date created</th>
            <th>Date modified</th>
        </tr>";

if (!$mysql->connect_errno) {

    $mysql->set_charset("utf8");

    $result = $mysql->query("select m.id,
       m.asset_id,
       m.type,
       m.profile,
       m.status,
       m.percentage,
       m.date_created,
       m.date_modified,
       concat(extractvalue(a.metadata, '//title/title'),
              if(extractvalue(a.metadata, '//title/title_brief') like '% NL%' and
                 extractvalue(a.metadata, '//title/title') not like '% NL%', ' NL', ''),
              if(extractvalue(a.metadata, '//title/title_brief') like '% HD' and
                 extractvalue(a.metadata, '//title/title') not like '%HD', ' HD', ''),
              if(extractvalue(a.metadata, '//title/title_brief') like '% FHD' and
                 extractvalue(a.metadata, '//title/title') not like '%FHD', ' FHD', ''),
              if(extractvalue(a.metadata, '//title/title_brief') like '% 4K' and
                 extractvalue(a.metadata, '//title/title') not like '%4K', ' 4K', ''))  as title,
       if(type = 'movie', (extractvalue(a.metadata, '//movie/asset_resolution')), '') as resolution
from mediaconvert_jobs m
         join asset a on m.asset_id = a.asset_id
order by m.date_created desc");

    $assets = array();

    if (!$result) {
        echo "<p> Error: " . mysqli_error($mysql) . "</p>";
    } else {

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $asset = array();

            $asset["id"] = $row["id"];
            $asset["asset_id"] = $row["asset_id"];
            $asset["type"] = $row["type"];
            $asset["profile"] = $row["profile"];
            $asset["status"] = $row["status"];
            $asset["percentage"] = $row["percentage"];
            $asset["title"] = $row["title"];
            $asset["resolution"] = $row["resolution"];
            $asset["date_created"] = $row["date_created"];
            $asset["date_modified"] = $row["date_modified"];

            array_push($assets, $asset);

        }

        $mysql->close();

        foreach ($assets as $asset) {
            echo '<tr><td>';

            echo $asset["id"];

            echo "</td><td>";

            echo '<a href="/remadevodui/?assetId=' . $asset["asset_id"] . '">' . $asset["asset_id"] . '</a>';

            echo "</td><td>";

            echo $asset["title"];

            echo "</td><td>";

            echo $asset["resolution"];

            echo "</td><td>";

            echo $asset["type"];

            echo "</td><td>";

            echo $asset["profile"];

            echo "</td><td>";

            echo $asset["status"];

            echo "</td><td>";

            echo "<div class=\"progress\" style=\"margin: 0;\">";

            $percentage = $asset["percentage"];

            switch ($asset["status"]) {
                case "ERROR":
                case "DOWNLOAD_FAILED":
                    $class = "progress-bar-danger";
                    break;
                case "DOWNLOADED":
                    $class = "progress-bar-success";
                    break;
                default:
                    $class = "progress-bar-info progress-bar-striped active";
                    break;
            }

            if ($percentage > 0) {
                echo "<div class=\"progress-bar $class\" role=\"progressbar\" style=\"width:$percentage%\">$percentage%</div>";
            }

            echo "</div>";

            echo "</td><td>";

            echo $asset["date_created"];

            echo "</td><td>";

            echo $asset["date_modified"];

            echo "</td></tr>";
        }
    }


} else {
    echo "<p> Horror: " . $mysql->connect_error . "</p>";
}

?>
</table>
</div>
</body>
</html>
