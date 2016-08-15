fetch('compressed.data')
.then(function(response) {
    return response.text();
}).then(function (compressedText) {
    return decompressPostalCodes(compressedText);
}).then(function (postalCodes) {
    window.postalCodes = postalCodes;

    setMarkerPostalCode(3815);
});

function decompressPostalCodes(rawData) {
    var rows = rawData.split('\n');
    var postalCode = parseInt(999);

    var postalCodes = {};

    rows.forEach(function (row, delta) {
        var splitRow = row.split(',');
        var skippedLines;
        var x, y;

        if (splitRow.length == 1) {
            skippedLines = parseInt(row);
            postalCode = postalCode + skippedLines;
        }
        else {
            postalCode++;
            x = parseFloat(splitRow[1]);
            y = 100 - parseFloat(splitRow[0]);

            postalCodes[postalCode] = {
                x: x,
                y: y
            }
        }
    });

    return postalCodes;
}

function setMarkerPostalCode(postalCode) {
    var marker = document.querySelector('.marker');
    marker.style.left = window.postalCodes[postalCode]['x'] + '%';
    marker.style.top= window.postalCodes[postalCode]['y'] + '%';
}

var postalCodeInput = document.querySelector('#postal-code');
postalCodeInput.addEventListener('keyup', function (event) {
    if (isNaN(this.value)) {
        this.value = '';
    }
    else {
        if (this.value > 999 && this.value < 10000) {
            console.log(this.value)
            setMarkerPostalCode(this.value);
        }
    }
});