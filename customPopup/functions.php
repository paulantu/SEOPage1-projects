<?php
// Enqueue Parent Theme Stylesheet
function astra_child_enqueue_styles() {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');


    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'));
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');




function custom_popup_html() {
    ?>
    <!-- Popup HTML -->
    <div id="custom-popup" style="display:none;">
        <div id="popup-content">
            <button id="close-popup" aria-label="Close Popup">&times;</button>
            <div id="popup-inner-content">
                <!-- Preloader -->
                <div id="popup-preloader" style="display: flex; justify-content: center; align-items: center; height: 100%;">
                    <div class="spinner"></div>
                </div>
                
                <!-- Shortcode Content -->
                <div id="popup-shortcode-content" style="display: none;">
                    <?php echo do_shortcode('[give_form id="1101"]'); ?>
                </div>
            </div>
        </div>
    </div>
    
<style>
        #custom-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 15px;
            overflow: auto;
        }

        /* Popup Content Box */
        #popup-content {
            background: #fff;
            border-radius: 12px;
            max-width: 50%;
            width: 100%;
            height: auto;
            max-height: 90vh;
            overflow-y: auto;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Close Icon */
        #close-popup {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
        }

        #close-popup:hover {
            color: #ff0000;
        }

        /* Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Inner Content Padding */
        #popup-inner-content {
            padding: 20px;
        }

        /* Fade-in Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #popup-content {
                max-width: 90%;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            #popup-content {
                font-size: 14px;
            }

            #close-popup {
                font-size: 20px;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const openPopup = document.getElementById("header-donation-button");
            const closePopup = document.getElementById("close-popup");
            const popup = document.getElementById("custom-popup");
            const preloader = document.getElementById("popup-preloader");
            const shortcodeContent = document.getElementById("popup-shortcode-content");
            
            // Track if popup is already open (to prevent multiple timers)
            let popupOpen = false;
            
            // Open popup on button click
            openPopup.addEventListener("click", function () {
                // Don't do anything if popup is already open
                if (popupOpen) return;
                
                popupOpen = true;
                popup.style.display = "flex";
                preloader.style.display = "flex";
                shortcodeContent.style.display = "none";
                
                // Simple fixed delay - make it long enough for the form to fully load
                setTimeout(function() {
                    preloader.style.display = "none";
                    shortcodeContent.style.display = "block";
                }, 3000); // 3 seconds should be plenty of time
            });

            // Close popup on close button click
            closePopup.addEventListener("click", function () {
                popup.style.display = "none";
                popupOpen = false;
                
                // Reset states immediately
                setTimeout(function() {
                    preloader.style.display = "flex";
                    shortcodeContent.style.display = "none";
                }, 100);
            });

            // Close popup when clicking outside of the content
            popup.addEventListener("click", function (event) {
                if (event.target === popup) {
                    popup.style.display = "none";
                    popupOpen = false;
                    
                    // Reset states immediately
                    setTimeout(function() {
                        preloader.style.display = "flex";
                        shortcodeContent.style.display = "none";
                    }, 100);
                }
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_popup_html');