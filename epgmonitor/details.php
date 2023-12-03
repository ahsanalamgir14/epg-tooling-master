<?php

require_once 'utils.php';
require_once 'config.php';

//$made = new mysqli('tdbs18-adm.wxs.nl', 'epggrid', 'epggrid');

$made = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);

if (!$made->connect_errno) {


    $made->set_charset("utf8");

    $source = getParameter("source");
    $id = getParameter("id");

    if ($source == MADE) {
        $query = "select programid,
       prnshow,
       prnepisode,
       extractvalue(programinformation, '//Season')      as                season,
       extractvalue(programinformation, '//Episode')     as                episode,
       extractvalue(programinformation, '//Synopsis')    as                synopsis,
       extractvalue(programinformation, '//Title[@type=''episodeTitle'']') episode_title,
       extractvalue(programinformation, '//Genre/@href') as                genre,
       parentalrating,
       (select hash
        from mdm.image_registry i
        where i.programidtm = e.prnshow
           or i.programidtm = e.prnepisode
        limit 1)                                         as                image_hash
from madeepg.epgdata e
where id = ?";
    } else {
        $query = "select programid,
       prnshow,
       prnepisode,
       extractvalue(programinformation, '//Season')                                        as season,
       extractvalue(programinformation, '//Episode')                                       as episode,
       extractvalue(programinformation, '//Synopsis')                                      as synopsis,
       extractvalue(programinformation, '//Title[@type=''episodeTitle'']')                    episode_title,
       extractvalue(programinformation, '//Genre/@href')                                   as genre,
       parentalrating,
       (select hash from mdm.image_registry i where i.programidtm = e.programidtm limit 1) as image_hash
from mdm.epgdata_distribute e
where id = ?";
    }

    $stmt = $made->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($programid, $prnshow, $prnepisode, $season, $episode, $synopsis, $episodeTitle, $genre, $parental, $imageHash);
    $stmt->fetch();

    $details = array();

    $details["CRID"] = $programid;

    $details["Show"] = $prnshow;

    if ($prnepisode) {
        $details["Episode number"] = $prnepisode;
    }

    if ($imageHash) {
        $details["Image hash"] = $imageHash;
    }

    if ($episodeTitle) {
        $details["Episode title"] = $episodeTitle;
    }

    if ($season) {
        $details["Season"] = $season;
    }

    if ($episode) {
        $details["Episode"] = $episode;
    }

    if ($genre) {
        $genres = explode(' ', $genre);

        $genreCs = array();
        $nicamCs = array();

        for ($i = 0; $i < count($genres); $i++) {
            if (contains($genres[$i], "NicamWarningCS")) {
                array_push($nicamCs, extractUrn($genres[$i]));
            } else {
                array_push($genreCs, extractUrn($genres[$i]));
            }
        }

        $genreString = null;

        for ($i = 0; $i < count($genreCs); $i++) {
            if (!$genreString) {
                $genreString = $genreCs[$i];
            } else {
                $genreString = $genreString . ', ' . $genreCs[$i];
            }
        }

        $nicamString = null;

        for ($i = 0; $i < count($nicamCs); $i++) {
            if (!$nicamString) {
                $nicamString = getNicamWarning($nicamCs[$i]);
            } else {
                $nicamString = $nicamString . ', ' . getNicamWarning($nicamCs[$i]);
            }
        }

        if ($genreString) {
            $details["Genre"] = ucfirst($genreString);
        }

        if ($nicamString) {
            $details["Nicam warning"] = ucfirst($nicamString);
        }
    }

    if ($parental) {
        $details["Parental rating"] = $parental;
    }

    $details["Synopsis"] = $synopsis;

    header('Content-type: application/json');
    echo json_encode($details);
}

function contains($string, $pattern)
{
    if (strpos($string, $pattern) !== false) {
        return true;
    } else {
        return false;
    }
}

function extractUrn($urn)
{
    $strings = explode(':', $urn);
    return $strings[count($strings) - 1];
}

function getNicamWarning($nicam)
{
    $value = '';

    switch ($nicam) {
        case 'a':
            $value = 'angst';
            break;
        case 'g':
            $value = 'geweld';
            break;
        case 't':
            $value = 'grof taalgebruik';
            break;
        case 'h':
            $value = 'discriminatie';
            break;
        case 'd':
            $value = 'drugs- en/of alcoholgebruik';
            break;
        case 's':
            $value = 'seks';
            break;
    }
    return $value;
}