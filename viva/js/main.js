// copy to clipboard

jQuery(document).ready(function($) {
    $('#copy-to-clipboard').on('click', function(e) {
        e.preventDefault();
        
        // Store original content
        var $button = $(this);
        var originalContent = $button.html();
        var buttonText = $button.find('.elementor-button-text').text();
        
        // Update to "Copied!" state
        $button.html(`
            <span class="elementor-button-content-wrapper text-copied">
                <span class="elementor-button-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 520 520" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                        <g>
                            <g data-name="15-Checked">
                                <circle cx="208.52" cy="288.5" r="176.52" fill="#635aff" opacity="0.27058823529411763" data-original="#b0ef8f" class=""></circle>
                                <path fill="#635aff" d="m210.516 424.937-2.239-3.815c-34.2-58.27-125.082-181.928-126-183.17l-1.311-1.781 30.963-30.6 98.012 68.439c61.711-80.079 119.283-135.081 156.837-167.2C407.859 71.675 434.6 55.5 434.87 55.345l.608-.364H488l-5.017 4.468C353.954 174.375 214.1 418.639 212.707 421.093z" opacity="1" data-original="#009045" class=""></path>
                            </g>
                        </g>
                    </svg>
                </span>
                <span class="elementor-button-text">Copied!</span>
            </span>
        `);
        
        // Copy to clipboard
        var $tempInput = $('<input>');
        $('body').append($tempInput);
        $tempInput.val(buttonText).select();
        document.execCommand('copy');
        $tempInput.remove();
        
        // Reset after 2 seconds
        setTimeout(function() {
            $button.html(originalContent);
        }, 2000);
    });
});