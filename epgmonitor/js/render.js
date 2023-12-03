function render() {

    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");

    ctx.clearRect(0, 0, c.width, c.height);

    drawBackground(ctx);
    drawFillers(ctx);

    if (!Grid.epgitems) {
        return;
    }

    var maxTime = Grid.startTime.addHours(3);
    var channels = getChannelList();
    var maxEndTime = maxTime.addMinutes(-Grid.shiftX);

    for (var x1 = Grid.shiftY; x1 < MAX_ROWS + Grid.shiftY; x1++) {

        if (x1 >= channels.length) {
            break;
        }

        var ref = channels[x1]["ref"];
        var name = channels[x1]["name"];

        drawRow(ctx, x1 + 1 - Grid.shiftY + getCompensation(), name);

        if (!Grid.epgitems[ref]) {
            continue;
        }

        for (var x2 = 0; x2 < Grid.epgitems[ref].length; x2++) {

            var epgitem1 = Grid.epgitems[ref][x2];

            var row = x1 - Grid.shiftY + 1;

            if (row < 1 && row > MAX_ROWS) {
                continue;
            }

            var start = convertTime(epgitem1["s"]);
            var end = convertTime(epgitem1["e"]);


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

            if (row <= MAX_ROWS && row > 0 && startTime < endTime) {

                var search = false;

                if (epgitem1["t"] && epgitem1["t"].toLowerCase().contains(Grid.search)) {
                    search = true;
                }

                draw(ctx, row, startTime, endTime, epgitem1, search);
            }
        }
    }

    drawEdge(ctx, getDiff(maxTime, Grid.startTime), channels.length);

    var time = Grid.startTime.addMinutes(-Grid.shiftX);
    var minutes = 0;
    var before = time.addMinutes(-15);
    var warning = before.getTimezoneOffset() != time.getTimezoneOffset();
    var minStartTime = Grid.startTime.addMinutes(-Grid.shiftX);

    while (time.getTime() < maxEndTime.getTime()) {
        drawColumn(ctx, minutes, getTime(time), warning);
        if (warning) {
            var diff = getDiff(time, minStartTime);
            drawLine(ctx, diff, true);
        }
        minutes += 15;
        var newTime = new Date(time.getTime() + 15 * 60000);
        warning = newTime.getTimezoneOffset() != time.getTimezoneOffset();
        time = newTime;
    }

    var now = new Date();

    drawDate(ctx, getDay(minStartTime));

    if (now > minStartTime.getTime() && now < maxEndTime.getTime()) {
        time = getDiff(now, minStartTime);
        drawLine(ctx, time, false);
    }

    switch (Grid.mark) {
        case NPVR:
            drawFooter(ctx, "Highlight NPVR Restrictions - " + getDescription(Grid.source));
            break;
        case SOTV:
            drawFooter(ctx, "Highlight SOTV Restrictions - " + getDescription(Grid.source));
            break;
        case CUTV:
            drawFooter(ctx, "Highlight CUTV Restrictions - " + getDescription(Grid.source));
            break;
        case TRICKPLAY:
            drawFooter(ctx, "Highlight Trickplay Restrictions - " + getDescription(Grid.source));
            break;
        default:
            drawFooter(ctx, getDescription(Grid.source));
            break;
    }
}


function renderDetails(epgitem, channel) {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");

    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx, 180, 0);
    drawKpnLogo(ctx);
    drawHeading(ctx, epgitem["t"]);

    drawKey(ctx, "Start", 1);
    drawValue(ctx, convertTime(epgitem["s"]), 1);
    drawKey(ctx, "End", 2);
    drawValue(ctx, convertTime(epgitem["e"]), 2);

    var restriction = epgitem["r"];

    drawKey(ctx, "NPVR", 3);
    drawValue(ctx, getNpvr(restriction) > 0 ? "Restricted" : "Not restricted", 3);
    drawKey(ctx, "SOTV", 4);
    drawValue(ctx, getSotv(restriction) > 0 ? "Restricted" : "Not restricted", 4);
    drawKey(ctx, "CUTV", 5);
    drawValue(ctx, getCutv(restriction) > 0 ? "Restricted" : "Not restricted", 5);
    drawKey(ctx, "Trickplay", 6);
    drawValue(ctx, getTrickplay(restriction) > 0 ? "Restricted" : "Not restricted", 6);

    Database.loadProgramDetails(Grid.source, epgitem["i"], function (response) {
        if (epgitem["i"] == Grid.item) {
            renderDetailsAsync(response);
        }
    });
}

function renderDetailsAsync(details) {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");

    var i = 0;

    var hash = null;

    for (var key in details) {
        if (Object.prototype.hasOwnProperty.call(details, key)) {
            var value = details[key];
            if (value) {
                drawKey(ctx, key, 7 + i);

                if (key == "Synopsis") {
                    var chunks = stringDivider(value, 200, "\n").match(/[^\r\n]+/g);
                    for (var i2 = 0; i2 < chunks.length; i2++) {
                        drawValue(ctx, chunks[i2], 7 + i);
                        i++;
                    }
                } else if (key == "Image hash") {
                    hash = value;
                    drawValue(ctx, value, 7 + i);
                    i++;
                } else {
                    drawValue(ctx, value, 7 + i);
                    i++;
                }
            }
        }
    }

    if (hash) {
        drawPoster(hash);
    } else {
        drawPlaceholder("No image available", COLOR_BLACK);
    }
}

function renderMenu() {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);


    drawBackground(ctx);

    drawHeading(ctx, "EPG Data Monitor");
    drawCommandLine(ctx, "Choose input:", 1);
    drawCommandLine(ctx, "[1] " + MADE_DESC, 2);
    drawCommandLine(ctx, "[2] " + MDM_DESC, 3);
}

function renderHelp() {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx);
    drawHeading(ctx, "Help");
    drawKey(ctx, "F1", 1);
    drawValue(ctx, "Help", 1);
    drawKey(ctx, "Enter", 2);
    drawValue(ctx, "Confirm input", 2);
    drawKey(ctx, "Escape", 3);
    drawValue(ctx, "Back to grid view / Cancel input action / Remove highlights", 3);
    drawKey(ctx, "Delete", 4);
    drawValue(ctx, "Back to main menu", 4);
    drawKey(ctx, "Arrow keys", 5);
    drawValue(ctx, "Browse grid horizontally & vertically", 5);
    drawKey(ctx, "Scroll wheel", 6);
    drawValue(ctx, "Browse grid horizontally", 6);
    drawKey(ctx, "Page up/down", 7);
    drawValue(ctx, "Browse grid horizontally (fast-forward)", 7);
    drawKey(ctx, "S", 8);
    drawValue(ctx, "Search programs", 8);
    drawKey(ctx, "F", 9);
    drawValue(ctx, "Filter channels", 9);
    drawKey(ctx, "6", 10);
    drawValue(ctx, "Highlight NPVR restrictions", 10);
    drawKey(ctx, "7", 11);
    drawValue(ctx, "Highlight SOTV restrictions", 11);
    drawKey(ctx, "8", 12);
    drawValue(ctx, "Highlight CUTV restrictions", 12);
    drawKey(ctx, "9", 13);
    drawValue(ctx, "Highlight Trickplay restrictions", 13);
}

function renderOptions(description, error, mouseX, mouseY) {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx);
    drawHeading(ctx, description);
    drawCommandLine(ctx, 'Select date:', 1);
    if (error) {
        drawErrorLine(ctx, error, 4);
    }
    var calWidth = CAL_WIDTH * CAL_ITEMS_X + (CAL_ITEMS_X - 1) * CAL_SPACING_X + 2 * CAL_EDGE_X;
    var currentDate = new Date();
    var year1;
    var year2 = currentDate.getFullYear();
    var year3;
    var month1;
    var month2 = currentDate.getMonth() + 1;
    var month3;

    if (month2 > 11) {
        year3 = year2 + 1;
        month3 = 1;
    } else {
        year3 = year2;
        month3 = month2 + 1;
    }

    if (month2 < 2) {
        year1 = year2 - 1;
        month1 = 12;
    } else {
        year1 = year2;
        month1 = month2 - 1;
    }

    var date1 = drawCalendar(ctx, FACTOR_Y * 2 + OFFSET_TEXT, FACTOR_Y * 6 + OFFSET_TEXT, mouseX, mouseY, year1, month1);
    var date2 = drawCalendar(ctx, FACTOR_Y * 3 + OFFSET_TEXT + calWidth, FACTOR_Y * 6 + OFFSET_TEXT, mouseX, mouseY, year2, month2);
    var date3 = drawCalendar(ctx, FACTOR_Y * 4 + OFFSET_TEXT + calWidth * 2, FACTOR_Y * 6 + OFFSET_TEXT, mouseX, mouseY, year3, month3);

    Grid.selectedDate = date1 != null ? date1 : date2 != null ? date2 : date3 != null ? date3 : null;
}

function renderLoadingScreen(text) {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx);
    drawJumbotron(ctx, text);
}

function renderFilter() {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx);
    drawHeading(ctx, "Filter channels");
    drawCommandLine(ctx, "> " + Input.getText() + '_', 1);
}

function renderSearch() {
    var c = document.getElementById("myCanvas");
    var ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
    drawBackground(ctx);
    drawHeading(ctx, "Search programs");
    drawCommandLine(ctx, "> " + Input.getText() + '_', 1);
}