<!DOCTYPE html>
<html lang="en">
<head>
    <title>TM Tooling
        <?php

        $host = substr(gethostname(), 0, 1);

        echo strtoupper($host);

        ?>
    </title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
</head>
<body>
<div class="jumbotron">
    <div class="container">
        <h1 class="display-3">
            TM Tooling
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
        <p>Welcome <?php echo ucwords(str_replace(".", ". ", $_SERVER["PHP_AUTH_USER"])); ?></p>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <p>
            <h1>EPG</h1>
        </div>
        <div class="col-md-4">
            <h3>AVS Dashboard</h3>
            <p>Monitoring tool for AVS 6.x EPG ingestion.</p>
            <p><a href="avs/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>EPG Monitor</h3>
            <p>Monitoring tool for MADE EPG data.</p>
            <p><a href="epgmonitor/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>EPG Restriction Manager</h3>
            <p>EPG Restriction Manager.</p>
            <p><a href="restriction-manager/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>MADE UI</h3>
            <p>User interface for MADE EPG.</p>
            <p><a href="ui/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>EPG Image Uploader</h3>
            <p>Upload EPG Images for CQ.</p>
            <p><a href="epgimageui/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <p>
            <h1>VOD</h1>
        </div>
        <div class="col-md-4">
            <h3>VOD Asset Analyzer</h3>
            <p>User interface for REMADE VOD.</p>
            <p><a href="analyzeasset/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>VOD Monitoring</h3>
            <p>Monitoring for REMADE VOD.</p>
            <p><a href="vod/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>Remade VOD UI</h3>
            <p>UI for REMADE VOD.</p>
            <p><a href="remadevodui/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
        <div class="col-md-4">
            <h3>MediaConvert Jobs</h3>
            <p>Monitoring for AWS MediaConvert jobs.</p>
            <p><a href="mediaconvert/" class="btn btn-primary" role="button">Open »</a></p>
        </div>
    </div>
    <hr>
    <footer>
        <p>© KPN <?php echo date("Y"); ?></p>
    </footer>
</div>
</body>
</html>
