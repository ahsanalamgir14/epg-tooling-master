const CAL_ITEMS_X = 7;
const CAL_ITEMS_Y = 6;
const CAL_EDGE_X = 20;
const CAL_EDGE_Y = 20;
const CAL_SPACING_X = 10;
const CAL_SPACING_Y = 10;
const CAL_WIDTH = 50;
const CAL_HEIGHT = 50;

const CAL_MONTHS = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

function drawCalendar(ctx, offsetX, offsetY, mouseX, mouseY, year, month) {

    var selectedDate = null;

    //ctx.clearRect(0, 0, canvas.width, canvas.height); // for demo
    ctx.fillStyle = COLOR_BLUE_LIGHT;
    ctx.fillStyle = COLOR_GREY;
    var width = CAL_WIDTH * CAL_ITEMS_X + (CAL_ITEMS_X - 1) * CAL_SPACING_X + 2 * CAL_EDGE_X;
    var height = CAL_HEIGHT * (CAL_ITEMS_Y + 1) + CAL_ITEMS_Y * CAL_SPACING_Y + 2 * CAL_EDGE_Y;
    ctx.fillRect(offsetX, offsetY, width, height);

    var days = getDaysInMonth(month - 1, year);
    var headerX = CAL_EDGE_X + offsetX;
    var headerY = CAL_EDGE_Y + offsetY;
    var headerWidth = CAL_WIDTH * CAL_ITEMS_X + (CAL_ITEMS_X - 1) * CAL_SPACING_X;
    ctx.fillStyle = COLOR_GREEN;
    ctx.fillRect(headerX, headerY, headerWidth, CAL_HEIGHT);
    ctx.font = '20px Arial';
    ctx.textBaseline = "middle";
    ctx.textAlign = "center";
    ctx.fillStyle = 'white';
    var headerTextX = headerX + headerWidth / 2;
    var headerTextY = headerY + CAL_HEIGHT / 2;
    ctx.fillText(CAL_MONTHS[days[0].getMonth()] + " " + days[0].getFullYear(), headerTextX, headerTextY);
    var column = getDayNumber(days[0]) - 1;
    var row = 0;
    for (var i = 0; i < days.length; i++) {
        var x = CAL_EDGE_X + (CAL_WIDTH + CAL_SPACING_X) * column;
        var y = CAL_EDGE_Y + (CAL_HEIGHT + CAL_SPACING_Y) * (row + 1);
        ctx.beginPath();
        ctx.rect(x + offsetX, y + offsetY, CAL_WIDTH, CAL_HEIGHT);
        if (ctx.isPointInPath(mouseX, mouseY)) {
            ctx.fillStyle = COLOR_GREEN;
            selectedDate = days[i];
        } else {
            ctx.fillStyle = COLOR_BLUE_DARK;
        }
        ctx.fill();
        ctx.fillStyle = 'white';
        ctx.fillText(days[i].getDate().toString(), x + (CAL_WIDTH / 2) + offsetX, y + (CAL_HEIGHT / 2) + offsetY);
        if (column > 5) {
            column = 0;
            row++;
        } else {
            column++;
        }
    }
    ctx.textAlign = 'start';
    ctx.textBaseline = "top";
    return selectedDate;
}

function getDaysInMonth(month, year) {
    var date = new Date(year, month, 1);
    var days = [];
    while (date.getMonth() === month) {
        days.push(new Date(date));
        date.setDate(date.getDate() + 1);
    }
    return days;
}

function getDayNumber(date) {
    return date.getDay() == 0 ? 7 : date.getDay();
}