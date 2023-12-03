var Database = (function () {

    function get(url, success, error) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.send(null);
        xhr.onreadystatechange = function () {
            var DONE = 4; // readyState 4 means the request is done.
            var OK = 200; // status 200 is a successful return.
            if (xhr.readyState === DONE) {
                if (xhr.status === OK) {
                    success(JSON.parse(xhr.responseText));
                } else {
                    error(JSON.parse(xhr.responseText));
                }
            }
        };
    }

    function loadChannelDetails() {
        get('channels.php', function (response) {
            Grid.channels = response;
            Grid.state = MENU;
            renderMenu(ctx);
        });
    }

    function loadEpgItems(source, date) {
        get('epgdata.php?source=' + source + '&date=' + date, function (response) {
            Grid.epgitems = response;
            Grid.state = GRID;
            Grid.startTime = convertTime(date + ' 00:00:00');
            render();
        }, function (error) {
            Grid.state = OPTIONS;
            renderOptions(getDescription(Grid.source), error["error"]);
        });
    }

    function loadProgramDetails(source, id, callback) {
        get('details.php?source=' + source + '&id=' + id, function (response) {
            callback(response);
        });
    }

    return {
        loadChannelDetails: loadChannelDetails,
        loadEpgItems: loadEpgItems,
        loadProgramDetails: loadProgramDetails
    }
})();