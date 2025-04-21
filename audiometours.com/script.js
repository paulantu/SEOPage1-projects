// onclick button icon change


jQuery(document).ready(function() {
    jQuery("#banner-audio-button .elementor-button").click(function(e) {
        e.preventDefault();
        
        var $button = jQuery(this);
        var $iconContainer = $button.find(".elementor-button-icon");
        
        var playIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" fill="none">
            <g clip-path="url(#clip0_138_1934)">
                <path d="M21.2721 2.20492C12.2565 -2.96653 4.94727 1.26999 4.94727 11.6599V88.3328C4.94727 98.7331 12.2565 102.964 21.2721 97.7975L88.288 59.3643C97.3066 54.1911 97.3066 45.8096 88.288 40.6375L21.2721 2.20492Z" fill="#48F7EA"></path>
            </g>
            <defs>
                <clipPath id="clip0_138_1934">
                    <rect width="100" height="100" fill="white"></rect>
                </clipPath>
            </defs>
        </svg>`;
        
        var pauseIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" fill="none">
            <g clip-path="url(#clip0_138_1934_pause)">
                <path d="M15 5H35V95H15V5Z" fill="#48F7EA"></path>
                <path d="M65 5H85V95H65V5Z" fill="#48F7EA"></path>
            </g>
            <defs>
                <clipPath id="clip0_138_1934_pause">
                    <rect width="100" height="100" fill="white"></rect>
                </clipPath>
            </defs>
        </svg>`;
        
        // Toggle between icons only
        if ($button.hasClass("playing")) {
            $iconContainer.html(playIcon);
            $button.removeClass("playing");
        } else {
            $iconContainer.html(pauseIcon);
            $button.addClass("playing");
        }
    });
});