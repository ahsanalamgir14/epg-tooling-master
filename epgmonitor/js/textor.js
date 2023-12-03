const NUMPAD_DIFF = 48;

var Input = (function () {

    var value = '';

    function addChar(char) {
        if (char == KEY_BACKSPACE) {
            removeChar();
            return;
        }

        if (char >= KEY_NUM_0 && char <= KEY_NUM_9) {
            char -= NUMPAD_DIFF;
        }

        if (char == KEY_MINUS || char == KEY_DASH || char == KEY_DASH_FF) {
            value += '-';
        } else {
            value += String.fromCharCode(char);
        }
    }

    function clear() {
        value = '';
    }

    function getText() {
        return value;
    }

    function validateKey(key) {
        return (key >= KEY_0 && key <= KEY_9) || (key >= KEY_A && key <= KEY_Z) || (key >= KEY_NUM_0 && key <= KEY_NUM_9) || key == KEY_SPACE || key == KEY_MINUS || key == KEY_DASH || key == KEY_DASH_FF || key == KEY_BACKSPACE;
    }

    function removeChar() {
        value = value.slice(0, -1);
    }

    return {
        addChar: addChar,
        getText: getText,
        clear: clear,
        validateKey: validateKey
    }
})();