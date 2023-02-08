function addChar(button) {
    let search_field = document.getElementById('s');
    let current_pos = theCursorPosition(search_field);
    let orig_value = search_field.value;
    search_field.value = orig_value.substring(0, current_pos) + button.value.trim() + orig_value.substring(current_pos);

    search_field.focus();

    return true;
}

function theCursorPosition(ofThisInput) {
    // set a fallback cursor location
    let theCursorLocation = 0;

    // find the cursor location via IE method...
    if (document.selection) {
        ofThisInput.focus();
        let theSelectionRange = document.selection.createRange();
        theSelectionRange.moveStart('character', -ofThisInput.value.length);
        theCursorLocation = theSelectionRange.text.length;
    } else if (ofThisInput.selectionStart || ofThisInput.selectionStart === 0) {
        // or the FF way
        theCursorLocation = ofThisInput.selectionStart;
    }
    return theCursorLocation;
}
