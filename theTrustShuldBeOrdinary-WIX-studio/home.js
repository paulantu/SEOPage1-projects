// Velo API Reference: https://www.wix.com/velo/reference/api-overview/introduction

$w.onReady(function () {
    updateTime();
    setInterval(updateTime, 1000);
});

function updateTime() {
    let now = new Date();
    
    // Formatting Date
    let day = String(now.getDate()).padStart(2, '0');
    let month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    let year = String(now.getFullYear()).slice(-2); // Get last 2 digits of year
    
    // Formatting Time
    let hours = String(now.getUTCHours()).padStart(2, '0'); // GMT Time
    let minutes = String(now.getUTCMinutes()).padStart(2, '0');
    let seconds = String(now.getUTCSeconds()).padStart(2, '0');

    let formattedTime = `${day}.${month}.${year} at ${hours}:${minutes}:${seconds} GMT`;
    
    // Apply bold styling to "[ 09 TRUTH DROPS ]"
    let styledText = `<span style="color:rgb(0 0 0 / 50%) !important; font-size:17px !important;">${formattedTime}</span> <span style="font-weight:700; color:rgba(38, 38, 38); font-size:17px !important;">[ 09 TRUTH DROPS ]</span>`;

    $w("#text29").html = styledText + " " + styledText + " " + styledText + " " + styledText; // Using .html to render styled text
    $w("#text46").html = styledText + " " + styledText + " " + styledText + " " + styledText;
}
