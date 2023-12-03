function draw(ctx, row, start, end, epgitem, searched) {
    var width = (end - start) * FACTOR_X;
    var offset = OFFSET_X;
    start *= FACTOR_X;
    start += offset + FACTOR_Y;

    var height = row * FACTOR_Y + FACTOR_Y;


    if ((Grid.mark == NPVR && getNpvr(epgitem["r"]) > 0) || (Grid.mark == SOTV && getSotv(epgitem["r"]) > 0) || (Grid.mark == CUTV && getCutv(epgitem["r"]) > 0) || (Grid.mark == TRICKPLAY && getTrickplay(epgitem["r"]) > 0)) {
        ctx.fillStyle = COLOR_ORANGE;
    } else if (searched && Grid.search) {
        ctx.fillStyle = COLOR_GREEN;
    } else {
        ctx.fillStyle = COLOR_BLUE_DARK;
    }

    ctx.strokeStyle = COLOR_BLUE_LIGHT;
    ctx.font = FONT_BOLD;
    ctx.textBaseline = "top";
    ctx.fillRect(start, height, width, FACTOR_Y);
    ctx.lineWidth = LINE_WIDTH;
    ctx.strokeRect(start, height, width, FACTOR_Y);
    ctx.fillStyle = COLOR_WHITE;
    ctx.fillText(epgitem.t, start + OFFSET_TEXT, height + OFFSET_TEXT);
}

function drawColumn(ctx, start, text, warning) {
    var offset = OFFSET_X;
    ctx.font = FONT_DEFAULT;
    ctx.textBaseline = "top";
    ctx.fillStyle = warning ? COLOR_ORANGE : COLOR_WHITE;
    ctx.fillText(text, ((start * FACTOR_X) + offset + FACTOR_Y) + OFFSET_TEXT, OFFSET_TEXT + FACTOR_Y);
}

function drawRow(ctx, row, text) {
    if (row <= MAX_ROWS && row > 0) {
        var height = (row + 1) * FACTOR_Y;
        ctx.font = FONT_BOLD;
        ctx.textBaseline = "top";
        ctx.fillStyle = COLOR_WHITE;
        ctx.fillText(text, FACTOR_Y + OFFSET_TEXT, height + OFFSET_TEXT);
    }
}

function drawBackground(ctx) {
    ctx.fillStyle = COLOR_BLUE_LIGHT;
    ctx.fillRect(0, 0, 180 * FACTOR_X + OFFSET_X + FACTOR_Y * 4, FACTOR_Y * (MAX_ROWS + 4));
}

function drawEdge(ctx, columns) {
    ctx.fillStyle = COLOR_BLUE_LIGHT;
    ctx.fillRect(columns * FACTOR_X + OFFSET_X + FACTOR_Y, 0, FACTOR_Y * 2, FACTOR_Y * (MAX_ROWS + 4));
}

function drawLine(ctx, time, warning) {
    var offset = OFFSET_X;
    ctx.fillStyle = warning ? COLOR_ORANGE : COLOR_GREEN;
    ctx.strokeStyle = COLOR_WHITE;
    ctx.lineWidth = LINE_WIDTH;
    ctx.fillRect(((time * FACTOR_X) + offset + FACTOR_Y), FACTOR_Y * 2, FACTOR_X / 4, MAX_ROWS * FACTOR_Y);
}

function drawFillers(ctx) {
    var width = 180 * FACTOR_X;
    var start = OFFSET_X + FACTOR_Y;
    ctx.fillStyle = COLOR_GREY;
    ctx.lineWidth = LINE_WIDTH;
    ctx.strokeStyle = COLOR_BLUE_LIGHT;
    for (var i = 0; i < MAX_ROWS; i++) {
        var height = (i + 1) * FACTOR_Y + FACTOR_Y;
        ctx.fillRect(start, height, width, FACTOR_Y);
        ctx.strokeRect(start, height, width, FACTOR_Y);
    }
}

function drawDate(ctx, date) {
    ctx.font = FONT_DEFAULT;
    ctx.textBaseline = "top";
    ctx.fillStyle = COLOR_WHITE;
    ctx.textAlign = 'right';
    ctx.fillText(date, OFFSET_X + FACTOR_Y - OFFSET_TEXT, OFFSET_TEXT + FACTOR_Y);
    ctx.textAlign = 'start';
}

function drawHeading(ctx, text) {
    ctx.fillStyle = COLOR_WHITE;
    ctx.font = FONT_HEADER;
    ctx.textBaseline = "top";
    ctx.fillText(text, FACTOR_Y * 2 + OFFSET_TEXT, FACTOR_Y * 2 + OFFSET_TEXT);
}

function drawKey(ctx, text, row) {
    ctx.font = FONT_BOLD;
    ctx.fillStyle = COLOR_WHITE;
    ctx.textBaseline = "top";
    ctx.fillText(text, FACTOR_Y * 2 + OFFSET_TEXT, FACTOR_Y * (3 + row) + OFFSET_TEXT);
}

function drawCommandLine(ctx, text, row) {
    ctx.fillStyle = COLOR_WHITE;
    ctx.font = FONT_INPUT;
    ctx.textBaseline = "top";
    ctx.fillText(text, FACTOR_Y * 2 + OFFSET_TEXT, FACTOR_Y * (3 + row) + OFFSET_TEXT);
}

function drawErrorLine(ctx, text, row) {
    ctx.fillStyle = COLOR_ORANGE;
    ctx.font = FONT_INPUT;
    ctx.textBaseline = "top";
    ctx.fillText(text, FACTOR_Y * 2 + OFFSET_TEXT, FACTOR_Y * (3 + row) + OFFSET_TEXT);
}

function drawValue(ctx, text, row) {
    ctx.font = FONT_DEFAULT;
    ctx.fillStyle = COLOR_WHITE;
    ctx.textBaseline = "top";
    ctx.fillText(text, FACTOR_Y * 7 + OFFSET_TEXT, FACTOR_Y * (3 + row) + OFFSET_TEXT);
}

function drawJumbotron(ctx, text) {
    ctx.fillStyle = COLOR_WHITE;
    ctx.font = FONT_JUMBO;
    ctx.textBaseline = "middle";
    ctx.textAlign = "center";
    ctx.fillText(text, SCREEN_X / 2, SCREEN_Y / 2);
    ctx.textAlign = 'start';
    ctx.textBaseline = "top";
}

function drawFooter(ctx, text) {
    ctx.fillStyle = COLOR_WHITE;
    ctx.font = FONT_DEFAULT;
    ctx.textBaseline = "top";
    ctx.textAlign = 'right';
    ctx.fillText(text, SCREEN_X - FACTOR_Y * 2 - OFFSET_TEXT, FACTOR_Y * 34 + OFFSET_TEXT);
    ctx.textAlign = 'start';
}

function drawKpnLogo(ctx) {

    var x = SCREEN_X - 135 - 2 * FACTOR_Y;
    var y = SCREEN_Y - 55 - 2 * FACTOR_Y;

    var img = new Image;
    img.onload = function () {
        ctx.drawImage(img, x, y); // Or at whatever offset you like
    };

    img.src = 'img/kpn.png';
}

function drawPoster(hash) {
    var img = new Image;

    drawPlaceholder("Loading...", COLOR_BLACK);

    var x = SCREEN_X - 2 * FACTOR_Y - IMAGE_X;
    var y = FACTOR_Y * 2;

    var url = 'https://images.tv.kpn.com/epg/' + hash + '/' + IMAGE_X + 'x' + IMAGE_Y + '.jpg';

    Grid.poster = url;

    img.onload = function () {
        if (url == Grid.poster) {
            ctx.drawImage(img, x, y); // Or at whatever offset you like
        }
    };

    img.src = url;
}

function drawPlaceholder(text, color) {
    ctx.fillStyle = color;
    var x = SCREEN_X - 2 * FACTOR_Y - IMAGE_X;
    var y = FACTOR_Y * 2;
    ctx.fillRect(x, y, IMAGE_X, IMAGE_Y);
    ctx.font = FONT_HEADER;
    ctx.fillStyle = COLOR_WHITE;
    ctx.textBaseline = "middle";
    ctx.textAlign = "center";
    ctx.fillText(text, x + IMAGE_X / 2, y + IMAGE_Y / 2);
    ctx.textAlign = 'start';
    ctx.textBaseline = "top";
}