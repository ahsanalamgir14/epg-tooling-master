String.prototype.contains = function (it) {
    return this.indexOf(it) != -1;
};

Date.prototype.addHours = function (h) {
    return new Date(this.getTime() + (h * 60 * 60 * 1000));
};

Date.prototype.addMinutes = function (m) {
    return new Date(this.getTime() + (m * 60000));
};

Date.prototype.yyyymmdd = function () {
    var month = '' + (this.getMonth() + 1),
        day = '' + this.getDate(),
        year = this.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
};

function stringDivider(str, width, spaceReplacer) {
    if (str.length > width) {
        var p = width;
        for (; p > 0 && str[p] != ' '; p--) {
        }
        if (p > 0) {
            var left = str.substring(0, p);
            var right = str.substring(p + 1);
            return left + spaceReplacer + stringDivider(right, width, spaceReplacer);
        }
    }
    return str;
}


function arrayContains(needle, arrhaystack) {
    return (arrhaystack.indexOf(needle) > -1);
}

function convertTime(date) {
    var t = date.split(/[- :]/);
    return new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4], t[5]));
}

function getDay(date) {
    var d = new Date(date || Date.now()),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate();
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    return [day, month].join('-');
}

function getTime(date) {
    return ("0" + date.getHours()).slice(-2) + ":" +
        ("0" + date.getMinutes()).slice(-2);
}

function getDiff(date1, date2) {
    return Math.round((date1.getTime() - date2.getTime()) / 60000);
}

/*
 function roundTimeQuarterHour(time) {
 var timeToReturn = new Date(time);

 timeToReturn.setMilliseconds(Math.floor(time.getMilliseconds() / 1000) * 1000);
 timeToReturn.setSeconds(Math.floor(timeToReturn.getSeconds() / 60) * 60);
 timeToReturn.setMinutes(Math.floor(timeToReturn.getMinutes() / 15) * 15);
 return timeToReturn;
 }
 */