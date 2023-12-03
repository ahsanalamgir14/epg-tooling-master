const NOTHING = 0;
const NPVR = 1;
const SOTV = 2;
const CUTV = 3;
const TRICKPLAY = 4;

const GRID = 0;
const DETAIL = 1;
const MENU = 2;
const FILTER = 3;
const MARK = 4;
const HELP = 5;
const LOADING = 6;
const OPTIONS = 7;
const SEARCH = 8;

const MADE = "made";
const MDM = "mdm";

const MADE_DESC = "MADE Stock database";
const MDM_DESC = "MDM Distribute database";

var Grid = {};

var canvas = document.getElementById("myCanvas");
var ctx = canvas.getContext("2d");

function init() {
    Grid.canvas = document.getElementById("myCanvas");
    Grid.context = canvas.getContext("2d");

    Grid.source = null;
    Grid.options = null;
    Grid.epgitems = null;
    Grid.startTime = null;
    Grid.channels = null;
    Grid.filter = null;
    Grid.search = null;
    Grid.state = LOADING;
    Grid.mark = NOTHING;

    Grid.item = null;
    Grid.poster = null;

    Grid.shiftX = 0;
    Grid.shiftY = 0;

    Grid.selectedDate = null;

    renderLoadingScreen("Loading channel info...");
    Database.loadChannelDetails();
}

function selectMade() {
    if (Grid.state == MENU) {
        Grid.source = MADE;
        Grid.state = OPTIONS;
        renderOptions(getDescription(Grid.source));
    }
}

function selectMdm() {
    if (Grid.state == MENU) {
        Grid.source = MDM;
        Grid.state = OPTIONS;
        renderOptions(getDescription(Grid.source));
    }
}

function shiftDown(delta) {
    if (Grid.state == GRID) {
        Grid.shiftY = Math.min(Grid.shiftY + delta, getChannelList().length - MAX_ROWS);
        render();
    }
}

function shiftUp(delta) {
    if (Grid.state == GRID) {
        Grid.shiftY = Math.max(0, Grid.shiftY - delta);
        render();
    }
}

function shiftLeft() {
    if (Grid.state == GRID) {
        Grid.shiftX += 15;
        render();
    }
}

function shiftRight() {
    if (Grid.state == GRID) {
        Grid.shiftX -= 15;
        render();
    }
}

function confirm() {
    switch (Grid.state) {
        case FILTER:
            filter();
            Input.clear();
            Grid.state = GRID;
            render();
            break;
        case SEARCH:
            search();
            Input.clear();
            Grid.state = GRID;
            render();
            break;
    }
}

function escape() {
    switch (Grid.state) {
        case OPTIONS:
            init();
            break;
        case GRID:
            clear();
            render();
            break;
        case FILTER:
        case SEARCH:
        case DETAIL:
            Grid.item = null;
            Grid.poster = null;
            Grid.state = GRID;
            render();
            break;
        case HELP :
            if (Grid.epgitems) {
                Grid.state = GRID;
                render();
            } else {
                Grid.state = MENU;
                renderMenu();
            }
            break;
    }
}

function help() {
    Grid.state = HELP;
    renderHelp();
}

function openFilter() {
    if (Grid.state == GRID) {
        Grid.state = FILTER;
        Input.clear();
        renderFilter();
    }
}

function openSearch() {
    if (Grid.state == GRID) {
        Grid.state = SEARCH;
        renderSearch();
    }
}

function typeCharacter(key) {
    if (Grid.state == FILTER || Grid.state == SEARCH) {
        if (Input.validateKey(key)) {
            Input.addChar(key);
            switch (Grid.state) {
                case FILTER:
                    renderFilter();
                    break;
                case SEARCH:
                    renderSearch();
                    break;
            }
            return true;
        }
    }
    return false;
}

function selectEpgItem(x, y) {
    var channels = getChannelList();

    if (!Grid.epgitems || !channels || Grid.state != GRID) {
        return;
    }

    var y1 = Math.floor(y / FACTOR_Y - 1);
    var x1 = Math.floor((x - OFFSET_X - FACTOR_Y) / FACTOR_X);

    var maxTime = Grid.startTime.addHours(3);
    var maxEndTime = maxTime.addMinutes(-Grid.shiftX);

    var index = y1 - 1 + Grid.shiftY;

    if (index >= channels.length || index < 0) {
        return;
    }

    var channel = channels[index];

    var epgitems = Grid.epgitems[channel["ref"]];

    if (!epgitems) {
        return;
    }

    for (var i = 0; i < epgitems.length; i++) {

        var epgitem = epgitems[i];

        var start = convertTime(epgitem["s"]);
        var end = convertTime(epgitem["e"]);


        var startTime = getDiff(start, Grid.startTime);

        var endTime = null;

        if (end.getTime() > maxEndTime.getTime()) {
            endTime = getDiff(maxEndTime, Grid.startTime);
        } else {
            endTime = getDiff(end, Grid.startTime);
        }

        startTime = startTime + Grid.shiftX;
        endTime = endTime + Grid.shiftX;

        startTime = startTime < 0 ? 0 : startTime;

        if (x1 >= startTime && x1 <= endTime) {
            Grid.item = epgitem["i"];
            Grid.state = DETAIL;
            renderDetails(epgitem, channel);
        }
    }
}

function selectDate() {
    if (Grid.state == OPTIONS && Grid.selectedDate) {
        Grid.state = LOADING;
        renderLoadingScreen("Loading " + getDescription(Grid.source) + "...");
        Input.clear();
        Database.loadEpgItems(Grid.source, Grid.selectedDate.yyyymmdd());
    }
}

function filter() {
    Grid.shiftY = 0;

    var expressions = Input.getText().toLowerCase().split(" or ");

    Grid.filter = [];

    for (var channelCount = 0; channelCount < Grid.channels.length; channelCount++) {
        var channel = Grid.channels[channelCount];

        var name = channel["name"];

        if (!name) {
            continue;
        }

        for (var expressionCount = 0; expressionCount < expressions.length; expressionCount++) {
            var expression = expressions[expressionCount].trim();
            if (expression != '' && name.toLowerCase().contains(expression)) {
                if (!arrayContains(channel, Grid.filter)) {
                    Grid.filter.push(channel);
                }
            }
        }
    }

    if (Grid.filter.length <= 0) {
        Grid.filter = null;
    }

    render();
}

function search() {
    var search = Input.getText().trim().toLowerCase();
    if (search) {
        Grid.search = search;
    } else {
        Grid.search = null;
    }
    Grid.mark = NOTHING;

    render();
}

function markNpvr() {
    if (Grid.state == GRID) {
        if (Grid.mark == NPVR) {
            clear();
        } else {
            Grid.mark = NPVR;
            Grid.search = null;
        }
        render();
    }
}

function markSotv() {
    if (Grid.state == GRID) {
        if (Grid.mark == SOTV) {
            clear();
        } else {
            Grid.mark = SOTV;
            Grid.search = null;
        }
        render();
    }
}

function markCutv() {
    if (Grid.state == GRID) {
        if (Grid.mark == CUTV) {
            clear();
        } else {
            Grid.mark = CUTV;
            Grid.search = null;
        }
        render();
    }
}

function markTrickplay() {
    if (Grid.state == GRID) {
        if (Grid.mark == TRICKPLAY) {
            clear();
        } else {
            Grid.mark = TRICKPLAY;
            Grid.search = null;
        }
        render();
    }
}

function refreshCalendar(x, y) {
    if (Grid.state == OPTIONS) {
        renderOptions(getDescription(Grid.source), null, x, y);
    }
}

function clear() {
    Grid.mark = NOTHING;
    Grid.search = null;
    render();
}

function format(str) {
    if (str.contains("dtvchannels")) {
        return str.replace("dtvchannels", "dtvchannels_");
    } else {
        var parts = [];
        parts.push(str.slice(0, 8));
        parts.push(str.slice(8, 12));
        parts.push(str.slice(12, 16));
        parts.push(str.slice(16, 20));
        parts.push(str.slice(20, 32));
        return parts.join('-');
    }
}

function getCompensation() {
    if (getChannelList().length - MAX_ROWS < 1) {
        return Grid.shiftY;
    } else if (Grid.shiftY > getChannelList().length - MAX_ROWS) {
        return Grid.shiftY - getChannelList().length + MAX_ROWS;
    } else {
        return 0;
    }
}

function getChannelList() {
    if (Grid.filter != null) {
        return Grid.filter;
    } else {
        return Grid.channels;
    }
}

function getNpvr(restriction) {
    return (restriction & 1 ) == 1;
}

function getSotv(restriction) {
    return (restriction & 2) == 2;
}

function getCutv(restriction) {
    return (restriction & 4 ) == 4;
}

function getTrickplay(restriction) {
    return (restriction & 8 ) == 8;
}

function getDescription(source) {
    var description = "unknown";
    switch (source) {
        case MADE:
            description = MADE_DESC;
            break;
        case MDM:
            description = MDM_DESC;
            break;
    }
    return description;
}

window.onload = function () {
    document.onkeydown = Controls.onKey;

    console.log("Q: Which browser?");

    if ("onmousewheel" in document) {
        console.log("A: Not Firefox!");
        document.onmousewheel = Controls.onScroll;
    } else {
        console.log("A: Firefox!");
        document.addEventListener('DOMMouseScroll', Controls.onScroll, false);
    }


    canvas.addEventListener('click', Controls.onClick, false);
    canvas.onmousemove = Controls.onMouseMove;
    init();
};