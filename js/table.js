function makeEditable(cell, rowId, column) {
    cell.contentEditable = true;
    cell.focus();

    cell.onblur = function() {
        cell.contentEditable = false;
        saveData(rowId, column, cell.innerText);
    };
}

function saveData(rowId, column, value) {
    fetch('save.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ rowId, column, value })
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);  // Optional: check response
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
