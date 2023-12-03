const KEY_BACKSPACE = 8;
const KEY_ENTER = 13;
const KEY_ESCAPE = 27;
const KEY_SPACE = 32;
const KEY_PAGE_UP = 33;
const KEY_PAGE_DOWN = 34;
const KEY_LEFT = 37;
const KEY_UP = 38;
const KEY_RIGHT = 39;
const KEY_DOWN = 40;
const KEY_DELETE = 46;
const KEY_0 = 48;
const KEY_1 = 49;
const KEY_2 = 50;
const KEY_3 = 51;
const KEY_6 = 54;
const KEY_7 = 55;
const KEY_8 = 56;
const KEY_9 = 57;
const KEY_A = 65;
const KEY_F = 70;
const KEY_S = 83;
const KEY_Z = 90;
const KEY_NUM_0 = 96;
const KEY_NUM_1 = 97;
const KEY_NUM_2 = 98;
const KEY_NUM_3 = 99;
const KEY_NUM_9 = 105;
const KEY_MINUS = 109;
const KEY_DASH_FF = 173;
const KEY_DASH = 189;
const KEY_F1 = 112;

var Controls = (function () {

    function onKey(e) {
        e = e || window.event;

        if (typeCharacter(e.keyCode)) {
            return;
        }

        switch (e.keyCode) {
            case KEY_1:
            case KEY_NUM_1:
                selectMade();
                break;
            case KEY_2:
            case KEY_NUM_2:
                selectMdm();
                break;
            case KEY_6:
                markNpvr();
                break;
            case KEY_7:
                markSotv();
                break;
            case KEY_8:
                markCutv();
                break;
            case KEY_9:
                markTrickplay();
                break;
            case KEY_ENTER:
                confirm();
                break;
            case KEY_ESCAPE:
                escape();
                break;
            case KEY_DELETE:
                init();
                break;
            case KEY_PAGE_UP:
                shiftUp(15);
                break;
            case KEY_PAGE_DOWN:
                shiftDown(15);
                break;
            case KEY_UP:
                shiftUp(1);
                break;
            case KEY_DOWN:
                shiftDown(1);
                break;
            case KEY_LEFT:
                shiftLeft();
                break;
            case KEY_RIGHT:
                shiftRight();
                break;
            case KEY_F:
                openFilter();
                break;
            case KEY_S:
                openSearch();
                break;
            case KEY_F1:
                help();
                e.preventDefault();
                break;
        }
    }

    function onScroll(e) {
        console.log("Scroll:", e);

        var scrollDelta = 0;

        if (e.deltaY) {
            scrollDelta = e.deltaY;
        } else if (e.detail) {
            scrollDelta = e.detail;
        }

        if (scrollDelta > 0) {
            shiftDown(1);
        } else if (scrollDelta < 0) {
            shiftUp(1);
        }
        return false;
    }

    function onClick(e) {
        var x = e.pageX - canvas.offsetLeft;
        var y = e.pageY - canvas.offsetTop;
        selectEpgItem(x, y);
        selectDate();
    }

    function onMouseMove(e) {
        var rect = this.getBoundingClientRect(),
            x = e.clientX - rect.left,
            y = e.clientY - rect.top;
        refreshCalendar(x, y);
    }

    return {
        onKey: onKey,
        onScroll: onScroll,
        onClick: onClick,
        onMouseMove: onMouseMove
    }
})();