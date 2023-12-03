<!DOCTYPE html>
<html lang="en">
<head>
    <title>VOD Monitor</title>
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
    <h1>VOD Monitor</h1>
    <table class=\"table\">
        <tr>
            <th>Asset ID</th>
            <th>Flow type</th>
            <th>Title</th>
            <th>Status</th>
            <th>VCAS</th>
            <th>EDGEWARE</th>
            <th>VCMS</th>
            <th>AVS</th>
            <th>Date modified</th>
        </tr>";

if (!$mysql->connect_errno) {

    $mysql->set_charset("utf8");

    $result = $mysql->query("select asset_id,
       flow_type,
       title,
       status,
       vcas,
       edgeware,
       vcms,
       avs,
       date_modified
from (select afsmax.asset_id,
             afs.flow_type,
             '1'                                                                             as 'type',
             concat(extractvalue(a.metadata, '//title/title'),
                    if(extractvalue(a.metadata, '//title/title_brief') like '% NL%' and
                       extractvalue(a.metadata, '//title/title') not like '% NL%', ' NL', ''),
                    if(extractvalue(a.metadata, '//title/title_brief') like '% HD' and
                       extractvalue(a.metadata, '//title/title') not like '%HD', ' HD', ''),
                    if(extractvalue(a.metadata, '//title/title_brief') like '% FHD' and
                       extractvalue(a.metadata, '//title/title') not like '%FHD', ' FHD', ''),
                    if(extractvalue(a.metadata, '//title/title_brief') like '% 4K' and
                       extractvalue(a.metadata, '//title/title') not like '%4K', ' 4K', '')) as title,
             a.status,
             vcas.status                                                                     as vcas,
             edgeware.status                                                                 as edgeware,
             vcms.status                                                                     as vcms,
             avs.status                                                                      as avs,
             afs.date_modified
      from (select afs.asset_id,
                   max(afs.flow_sequence_id) as flow_sequence_id
            from asset_flow_status afs
            where afs.date_created > timestamp(curdate()) - interval 1 day
            group by afs.asset_id) afsmax
               join asset_flow_status afs on afsmax.flow_sequence_id = afs.flow_sequence_id
               join asset a on afs.asset_id = a.asset_id
               left join asset_task_status vcas
                         on afs.asset_id = vcas.asset_id and afs.flow_sequence_id = vcas.flow_sequence_id and
                            vcas.task = 'VCAS'
               left join asset_task_status edgeware
                         on afs.asset_id = edgeware.asset_id and
                            afs.flow_sequence_id = edgeware.flow_sequence_id and
                            edgeware.task = 'EDGEWARE'
               left join asset_task_status vcms
                         on afs.asset_id = vcms.asset_id and afs.flow_sequence_id = vcms.flow_sequence_id and
                            vcms.task = 'VCMS'
               left join asset_task_status avs
                         on afs.asset_id = avs.asset_id and afs.flow_sequence_id = avs.flow_sequence_id and
                            avs.task = 'AVS'
      where afs.date_created > TIMESTAMP(CURDATE()) - interval 1 day
      union
      select ifnull(aw.asset_id, aw.asset_id_original),
             if(extractvalue(aw.metadata_original, '//AMS[@Asset_Class=''package'']/@Verb') = 'DELETE', 'delete (work)',
                if(a.asset_id is null, 'deploy (work)', 'update (work)')),
             '2'            as 'type',
             concat(extractvalue(aw.metadata_original, '//App_Data[@Name=''Title'']/@Value'),
                    if(extractvalue(aw.metadata_original, '//App_Data[@Name=''Title_Brief'']/@Value') like '% NL%' and
                       extractvalue(aw.metadata_original, '//App_Data[@Name=''Title'']/@Value') not like '% NL%', ' NL',
                       ''),
                    if(extractvalue(aw.metadata_original, '//App_Data[@Name=''Title_Brief'']/@Value') like '% HD' and
                       extractvalue(aw.metadata_original, '//App_Data[@Name=''Title'']/@Value') not like '%HD', ' HD',
                       ''),
                    if(extractvalue(aw.metadata_original, '//App_Data[@Name=''Title_Brief'']/@Value') like '% FHD' and
                       extractvalue(aw.metadata_original, '//App_Data[@Name=''Title'']/@Value') not like '%FHD', ' FHD',
                       ''),
                    if(extractvalue(aw.metadata_original, '//App_Data[@Name=''Title_Brief'']/@Value') like '% 4K' and
                       extractvalue(aw.metadata_original, '//App_Data[@Name=''Title'']/@Value') not like '%4K', ' 4K',
                       '')) as title,
             aw.status,
             NULL,
             NULL,
             NULL,
             NULL,
             aw.date_modified
      from asset_work aw
               left join asset a on aw.asset_id = a.asset_id
      where aw.date_created > timestamp(curdate()) - interval 1 day
     ) monitor
order by if(status = 'waiting', '1', if(status = 'inprogress', '2', if(status = 'init', '3', if(status = 'error', '4', '5')))), type desc, date_modified desc");

    $assets = array();

    if (!$result) {
        echo "<p> Error: " . mysqli_error($mysql) . "</p>";
    } else {

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $asset = array();

            $asset["asset_id"] = $row["asset_id"];
            $asset["flow_type"] = $row["flow_type"];
            $asset["title"] = $row["title"];
            $asset["status"] = $row["status"];
            $asset["vcas"] = $row["vcas"];
            $asset["edgeware"] = $row["edgeware"];
            $asset["vcms"] = $row["vcms"];
            $asset["avs"] = $row["avs"];
            $asset["date_modified"] = $row["date_modified"];

            array_push($assets, $asset);

        }

        $mysql->close();

        foreach ($assets as $asset) {

            switch ($asset["status"]) {
                case "active":
                case "inactive":
                    $class = "success";
                    break;
                case "error":
                    $class = "danger";
                    break;
                default:
                    $class = "";
                    break;
            }

            echo '<tr class="' . $class . '"><td>';

            echo '<a href="/remadevodui/?assetId=' . $asset["asset_id"] . '">' . $asset["asset_id"] . '</a>';

            echo "</td><td>";

            echo $asset["flow_type"];

            echo "</td><td>";

            echo $asset["title"];

            echo "</td><td>";

            echo $asset["status"];

            switch ($asset["vcas"]) {
                case "open":
                    $vcasClass = "warning";
                    break;
                case "processing":
                    $vcasClass = "info";
                    break;
                case "finished":
                    $vcasClass = "success";
                    break;
                case "error":
                    $vcasClass = "danger";
                    break;
                default:
                    $vcasClass = "";
                    break;
            }

            echo '</td><td class="' . $vcasClass . '">';

            if ($asset["vcas"] != null) {
                echo $asset["vcas"];
            } else {
                echo "-";
            }

            switch ($asset["edgeware"]) {
                case "open":
                    $edgewareClass = "warning";
                    break;
                case "processing":
                    $edgewareClass = "info";
                    break;
                case "finished":
                    $edgewareClass = "success";
                    break;
                case "error":
                    $edgewareClass = "danger";
                    break;
                default:
                    $edgewareClass = "";
                    break;
            }

            echo '</td><td class="' . $edgewareClass . '">';

            if ($asset["edgeware"] != null) {
                echo $asset["edgeware"];
            } else {
                echo "-";
            }

            switch ($asset["vcms"]) {
                case "open":
                    $vcmsClass = "warning";
                    break;
                case "processing":
                    $vcmsClass = "info";
                    break;
                case "finished":
                    $vcmsClass = "success";
                    break;
                case "error":
                    $vcmsClass = "danger";
                    break;
                default:
                    $vcmsClass = "";
                    break;
            }

            echo '</td><td class="' . $vcmsClass . '">';

            if ($asset["vcms"] != null) {
                echo $asset["vcms"];
            } else {
                echo "-";
            }

            switch ($asset["avs"]) {
                case "open":
                    $avsClass = "warning";
                    break;
                case "processing":
                    $avsClass = "info";
                    break;
                case "finished":
                    $avsClass = "success";
                    break;
                case "error":
                    $avsClass = "danger";
                    break;
                default:
                    $avsClass = "";
                    break;
            }

            echo '</td><td class="' . $avsClass . '">';

            if ($asset["avs"] != null) {
                echo $asset["avs"];
            } else {
                echo "-";
            }

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
