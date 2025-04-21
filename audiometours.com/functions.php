<?php


/*
Developer: Antu Paul
Organization: SEOPage1
Organization url: seopage1.net
Date: 21.04.25
*/



// Enqueue Parent Theme Stylesheet
function astra_child_enqueue_styles() {
    // Enqueue Parent Theme Styles
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Optionally, enqueue Child Theme Styles (if any custom styles are added)
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'));
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');







/**
 * Shortcode to display tour short info custom fields
 * Usage: [tour_short_info]
 */
function display_tour_short_info_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'tour_short_info'
    );
    
    $product_id = $atts['product_id'];
    
    // Get tour short info repeater field count
    $repeater_count = get_post_meta($product_id, 'tour_short_info', true);
    
    // If no data found, return empty
    if(empty($repeater_count) || !is_numeric($repeater_count)) {
        return '<div>No tour information available</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for the container
    echo '<style>
        .tour-info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 20px 0;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .tour-info-item {
            display: flex;
            align-items: center;
            width: 33.33%;
            margin-bottom: 15px;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .tour-info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #f85a2a;
        }
        .tour-info-icon img {
            width: 24px;
            height: auto;
        }
        .tour-info-title {
            color: #666;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .tour-info-item {
                width: 50%;
            }
        }
        @media (max-width: 480px) {
            .tour-info-item {
                width: 100%;
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="tour-info-container">';
    
    // Loop through repeater items
    for($i = 0; $i < $repeater_count; $i++) {
        $icon_id = get_post_meta($product_id, 'tour_short_info_' . $i . '_icon', true);
// var_dump($icon_id);
        $title = get_post_meta($product_id, 'tour_short_info_' . $i . '_title', true);
        
        if(!empty($icon_id) && !empty($title)) {
            echo '<div class="tour-info-item">';
            
            // Display icon as image
            echo '<div class="tour-info-icon">';
            
            // Get image URL from attachment ID
            $icon_url = wp_get_attachment_url($icon_id);
            
            if($icon_url) {
                echo '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($title) . '">';
            }
            
            echo '</div>';
            
            // Display title
            echo '<div class="tour-info-title">' . esc_html($title) . '</div>';
            
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('tour_short_info', 'display_tour_short_info_shortcode');







/**
 * Shortcode to display tour route map from SCF custom field in a responsive iframe
 * Usage: [tour_route_map]
 */
function display_tour_route_map_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
            'width' => '100%',
            'height' => '450px',
            'class' => 'tour-route-map',
        ),
        $atts,
        'tour_route_map'
    );
    
    $product_id = $atts['product_id'];
    $width = $atts['width'];
    $height = $atts['height'];
    $class = $atts['class'];
    
    // Get the tour_route_map field value using SCF
    if (function_exists('SCF')) {
        $map_url = SCF::get('tour_route_map', $product_id);
    } else {
        // Fallback if SCF function doesn't exist
        $map_url = get_post_meta($product_id, 'tour_route_map', true);
    }
    
    // If no map URL found, return message
    if (empty($map_url)) {
        return '<div class="no-map-message">No tour route map available for this product.</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for responsive iframe
    echo '<style>
        .responsive-iframe-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            margin: 20px 0;
        }
        .responsive-iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .responsive-iframe-container.' . esc_attr($class) . ' {
            padding-top: ' . (is_numeric(str_replace(array('px', '%'), '', $height)) ? '56.25%' : $height) . ';
        }
        @media (max-width: 768px) {
            .responsive-iframe-container {
                padding-top: 75%; /* Adjust aspect ratio for mobile */
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="responsive-iframe-container ' . esc_attr($class) . '">';
    echo '<iframe src="' . esc_url($map_url) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    echo '</div>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('tour_route_map', 'display_tour_route_map_shortcode');





/**
 * Shortcode to display about tour audio custom fields
 * Usage: [audio_previews]
 */
function display_audio_preview_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'audio_preview'
    );
    
    $product_id = $atts['product_id'];
    
    // Get audio preview repeater field count
    $repeater_count = get_post_meta($product_id, 'audio_preview', true);
    
    // If no data found, return empty
    if(empty($repeater_count) || !is_numeric($repeater_count)) {
        return '<div>No audio previews available</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for the container
    echo '<style>
        .audio-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .audio-preview-item {
            flex: 1 0 calc(25% - 20px);
            min-width: 200px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .audio-preview-title {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
            width: 100%;
        }
        .audio-controls {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .play-button {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ff6347;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s;
        }
        .play-button:hover {
            background-color: #ff4c2b;
        }
        .play-button svg {
            width: 40px;
            height: 40px;
            fill: white;
            margin-left: 4px; /* Slight offset for play icon */
            display: block; /* Ensure SVG is visible */
        }
        .play-button.playing svg {
            margin-left: 0; /* Reset for pause icon */
        }
        .time-display {
            color: #999;
            font-size: 12px;
            margin-right: 10px;
            min-width: 45px;
        }
        .progress-container {
            width: 100%;
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .progress-bar {
            flex-grow: 1;
            height: 4px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            background-color: #48F7EA;
            width: 0;
            border-radius: 2px;
        }
        /* Hide default audio controls */
        .hidden-audio {
            display: none;
        }
        
        /* Full-width modal audio player */
        .audio-modal {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #000;
            color: #fff;
            z-index: 9999;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
        }
        .audio-modal.active {
            transform: translateY(0);
        }
        .audio-modal-content {
            display: flex;
            align-items: center;
            padding: 0 20px;
            height: 80px;
        }
        .audio-modal-title {
            flex: 0 0 200px;
            font-weight: 600;
            margin-right: 20px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .audio-modal-controls {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .modal-play-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin: 0 15px 0 0;
        }
        .modal-play-button svg {
            width: 30px;
            height: 30px;
            fill: white;
        }
        .modal-time {
            font-size: 14px;
            font-family: monospace;
            margin-right: 15px;
            min-width: 50px;
        }
        .modal-time-end {
            margin-left: 15px;
        }
        .modal-progress-container {
            flex: 1;
            height: 8px;
            background-color: #444;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
        }
        .modal-progress-fill {
            height: 100%;
            background-color: #48F7EA;
            width: 0;
            border-radius: 4px;
        }
        .volume-control {
            display: flex;
            align-items: center;
            margin-left: 20px;
        }
        .volume-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-right: 10px;
        }
        .volume-button svg {
            width: 24px;
            height: 24px;
            fill: white;
        }
        .close-modal-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            margin-left: 15px;
        }
        .close-modal-button svg {
            width: 16px;
            height: 16px;
            fill: white;
        }
        
        @media (max-width: 992px) {
            .audio-preview-item {
                flex: 1 0 calc(33.33% - 20px);
            }
            .audio-modal-title {
                flex: 0 0 150px;
            }
        }
        @media (max-width: 768px) {
            .audio-preview-item {
                flex: 1 0 calc(50% - 20px);
            }
            .audio-modal-content {
                height: auto;
                padding: 15px;
                flex-wrap: wrap;
            }
            .audio-modal-title {
                flex: 1 0 100%;
                margin-bottom: 10px;
                margin-right: 0;
            }
            .audio-modal-controls {
                flex: 1 0 100%;
            }
        }
        @media (max-width: 480px) {
            .audio-preview-item {
                flex: 1 0 100%;
            }
            .audio-preview-container {
                padding: 15px;
                gap: 15px;
            }
            .modal-time {
                font-size: 12px;
                min-width: 40px;
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="audio-preview-container">';
    
    // Loop through repeater items
    for($i = 0; $i < $repeater_count; $i++) {
        $audio_id = get_post_meta($product_id, 'audio_preview_' . $i . '_audio', true);
        $file_name = get_post_meta($product_id, 'audio_preview_' . $i . '_file_name', true);
        
        if(!empty($audio_id)) {
            // Get audio URL from attachment ID
            $audio_url = wp_get_attachment_url($audio_id);
            
            if($audio_url) {
                $audio_id_attr = 'audio-preview-' . $i . '-' . $product_id;
                
                echo '<div class="audio-preview-item">';
                
                // Display title if available
                if(!empty($file_name)) {
                    echo '<div class="audio-preview-title">' . esc_html($file_name) . '</div>';
                }
                
                echo '<div class="audio-controls">';
                echo '<button class="play-button" data-audio-id="' . esc_attr($audio_id_attr) . '" data-title="' . esc_attr($file_name) . '" data-url="' . esc_url($audio_url) . '">';
                echo '<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>';
                echo '</button>';
                
                echo '<div class="progress-container">';
                echo '<div class="time-display">00:00</div>';
                echo '<div class="progress-bar" data-audio-id="' . esc_attr($audio_id_attr) . '">';
                echo '<div class="progress-fill"></div>';
                echo '</div>';
                echo '</div>';
                
                echo '<audio id="' . esc_attr($audio_id_attr) . '" class="hidden-audio">';
                echo '<source src="' . esc_url($audio_url) . '" type="audio/mpeg">';
                echo 'Your browser does not support the audio element.';
                echo '</audio>';
                
                echo '</div>';
                echo '</div>';
            }
        }
    }
    
    echo '</div>';
    
    // Add modal player HTML
    echo '<div class="audio-modal">
        <div class="audio-modal-content">
            <div class="audio-modal-title">Audio Track</div>
            <div class="audio-modal-controls">
                <button class="modal-play-button">
                    <svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>
                </button>
                <div class="modal-time">00:00</div>
                <div class="modal-progress-container">
                    <div class="modal-progress-fill"></div>
                </div>
                <div class="modal-time modal-time-end">-00:00</div>
                <div class="volume-control">
                    <button class="volume-button">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path>
                        </svg>
                    </button>
                </div>
                <button class="close-modal-button">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>';
    
    // JavaScript for custom audio player
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const playButtons = document.querySelectorAll(".play-button");
        const progressBars = document.querySelectorAll(".progress-bar");
        const audioElements = {};
        let currentModalAudio = null;
        
        // Modal elements
        const audioModal = document.querySelector(".audio-modal");
        const modalTitle = document.querySelector(".audio-modal-title");
        const modalPlayButton = document.querySelector(".modal-play-button");
        const modalProgress = document.querySelector(".modal-progress-container");
        const modalProgressFill = document.querySelector(".modal-progress-fill");
        const modalTime = document.querySelector(".modal-time");
        const modalTimeEnd = document.querySelector(".modal-time-end");
        const volumeButton = document.querySelector(".volume-button");
        const closeModalButton = document.querySelector(".close-modal-button");
        
        // Initialize all audio elements
        playButtons.forEach(button => {
            const audioId = button.getAttribute("data-audio-id");
            const audioTitle = button.getAttribute("data-title");
            const audioUrl = button.getAttribute("data-url");
            const audio = document.getElementById(audioId);
            
            audioElements[audioId] = {
                element: audio,
                playing: false,
                button: button,
                title: audioTitle,
                url: audioUrl,
                progressBar: document.querySelector(`.progress-bar[data-audio-id="${audioId}"]`),
                progressFill: document.querySelector(`.progress-bar[data-audio-id="${audioId}"] .progress-fill`),
                timeDisplay: button.parentNode.querySelector(".time-display")
            };
            
            // Play/pause functionality
            button.addEventListener("click", function() {
                const audioId = this.getAttribute("data-audio-id");
                const audioData = audioElements[audioId];
                
                // Pause all other audio elements
                Object.keys(audioElements).forEach(key => {
                    if (key !== audioId && audioElements[key].playing) {
                        audioElements[key].element.pause();
                        audioElements[key].playing = false;
                        audioElements[key].button.classList.remove("playing");
                        audioElements[key].button.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                    }
                });
                
                if (audioData.playing) {
                    // Pause audio
                    audioData.element.pause();
                    audioData.playing = false;
                    audioData.button.classList.remove("playing");
                    audioData.button.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                    
                    // Update modal if this is the current modal audio
                    if (currentModalAudio === audioId) {
                        modalPlayButton.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                    }
                } else {
                    // Play audio and open modal
                    audioData.element.play();
                    audioData.playing = true;
                    audioData.button.classList.add("playing");
                    audioData.button.innerHTML = \'<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>\';
                    
                    // Update modal
                    currentModalAudio = audioId;
                    modalTitle.textContent = audioData.title || "Audio Track";
                    modalPlayButton.innerHTML = \'<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>\';
                    
                    // Show the modal
                    audioModal.classList.add("active");
                    
                    // Update duration in the modal
                    if (audio.duration) {
                        updateDurationDisplay(audio.duration);
                    } else {
                        // For cases where duration isn\'t available immediately
                        audio.addEventListener("loadedmetadata", function() {
                            updateDurationDisplay(audio.duration);
                        }, { once: true });
                    }
                }
            });
            
            // Time update event
            audio.addEventListener("timeupdate", function() {
                const audioId = this.id;
                const audioData = audioElements[audioId];
                
                // Update progress bar
                const percent = (this.currentTime / this.duration) * 100;
                audioData.progressFill.style.width = `${percent}%`;
                
                // Update time display
                const currentMinutes = Math.floor(this.currentTime / 60);
                const currentSeconds = Math.floor(this.currentTime % 60);
                const formattedTime = `${currentMinutes.toString().padStart(2, "0")}:${currentSeconds.toString().padStart(2, "0")}`;
                audioData.timeDisplay.textContent = formattedTime;
                
                // Update modal if this is the current modal audio
                if (currentModalAudio === audioId) {
                    modalProgressFill.style.width = `${percent}%`;
                    modalTime.textContent = formattedTime;
                    
                    // Update remaining time
                    const remainingTime = this.duration - this.currentTime;
                    const remainingMinutes = Math.floor(remainingTime / 60);
                    const remainingSeconds = Math.floor(remainingTime % 60);
                    modalTimeEnd.textContent = `-${remainingMinutes.toString().padStart(2, "0")}:${remainingSeconds.toString().padStart(2, "0")}`;
                }
            });
            
            // Audio ended event
            audio.addEventListener("ended", function() {
                const audioId = this.id;
                const audioData = audioElements[audioId];
                
                audioData.playing = false;
                audioData.button.classList.remove("playing");
                audioData.button.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                audioData.progressFill.style.width = "0%";
                audioData.timeDisplay.textContent = "00:00";
                
                // Update modal if this is the current modal audio
                if (currentModalAudio === audioId) {
                    modalPlayButton.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                    modalProgressFill.style.width = "0%";
                    modalTime.textContent = "00:00";
                }
            });
        });
        
        // Helper function to update duration display
        function updateDurationDisplay(duration) {
            const durationMinutes = Math.floor(duration / 60);
            const durationSeconds = Math.floor(duration % 60);
            const totalDuration = `${durationMinutes.toString().padStart(2, "0")}:${durationSeconds.toString().padStart(2, "0")}`;
            modalTimeEnd.textContent = `-${totalDuration}`;
        }
        
        // Progress bar click functionality
        progressBars.forEach(progressBar => {
            progressBar.addEventListener("click", function(e) {
                const audioId = this.getAttribute("data-audio-id");
                const audioData = audioElements[audioId];
                const audio = audioData.element;
                
                // Calculate click position as a percentage of the progress bar width
                const rect = this.getBoundingClientRect();
                const clickPosition = (e.clientX - rect.left) / rect.width;
                
                // Set audio time based on click position
                audio.currentTime = clickPosition * audio.duration;
                
                // Update progress bar
                audioData.progressFill.style.width = `${clickPosition * 100}%`;
            });
        });
        
        // Modal play/pause button functionality
        if (modalPlayButton) {
            modalPlayButton.addEventListener("click", function() {
                if (!currentModalAudio) return;
                
                const audioData = audioElements[currentModalAudio];
                if (audioData.playing) {
                    // Pause audio
                    audioData.element.pause();
                    audioData.playing = false;
                    audioData.button.classList.remove("playing");
                    audioData.button.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                    modalPlayButton.innerHTML = \'<svg viewBox="0 0 24 24"><polygon points="8,5 8,19 19,12"></polygon></svg>\';
                } else {
                    // Play audio
                    audioData.element.play();
                    audioData.playing = true;
                    audioData.button.classList.add("playing");
                    audioData.button.innerHTML = \'<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>\';
                    modalPlayButton.innerHTML = \'<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>\';
                }
            });
        }
        
        // Modal progress bar functionality
        if (modalProgress) {
            modalProgress.addEventListener("click", function(e) {
                if (!currentModalAudio) return;
                
                const audioData = audioElements[currentModalAudio];
                const audio = audioData.element;
                
                // Calculate click position as a percentage of the progress bar width
                const rect = this.getBoundingClientRect();
                const clickPosition = (e.clientX - rect.left) / rect.width;
                
                // Set audio time based on click position
                audio.currentTime = clickPosition * audio.duration;
                
                // Update both progress bars
                audioData.progressFill.style.width = `${clickPosition * 100}%`;
                modalProgressFill.style.width = `${clickPosition * 100}%`;
            });
        }
        
        // Volume button functionality (toggle mute)
        if (volumeButton) {
            volumeButton.addEventListener("click", function() {
                if (!currentModalAudio) return;
                
                const audioData = audioElements[currentModalAudio];
                const audio = audioData.element;
                
                if (audio.muted) {
                    audio.muted = false;
                    volumeButton.innerHTML = \'<svg viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path></svg>\';
                } else {
                    audio.muted = true;
                    volumeButton.innerHTML = \'<svg viewBox="0 0 24 24"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"></path></svg>\';
                }
            });
        }
        
        // Close modal button
        if (closeModalButton) {
            closeModalButton.addEventListener("click", function() {
                audioModal.classList.remove("active");
                
                
            });
        }
    });
    </script>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('audio_previews', 'display_audio_preview_shortcode');






/**
 * Shortcode to display tour highlights info custom fields
 * Usage: [highlights_info]
 */
function display_highlights_info_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'highlights_info'
    );
    
    $product_id = $atts['product_id'];
    
    // Get tour short info repeater field count
    $repeater_count = get_post_meta($product_id, 'highlights', true);
    
    // If no data found, return empty
    if(empty($repeater_count) || !is_numeric($repeater_count)) {
        return '<div>No Highlights information available</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for the container
    echo '<style>
        .highlights-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .highlights-item {
            display: flex;
            align-items: center;
            width: 50%;
            margin-bottom: 15px;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .highlights-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #f85a2a;
        }
        .highlights-icon img {
            width: 24px;
            height: auto;
        }
        .highlights-title {
            color: #666;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .highlights-item {
                width: 50%;
            }
        }
        @media (max-width: 480px) {
            .highlights-item {
                width: 100%;
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="highlights-container">';
    
    // Loop through repeater items
    for($i = 0; $i < $repeater_count; $i++) {
        $icon_id = get_post_meta($product_id, 'highlights_' . $i . '_icon', true);
// var_dump($icon_id);
        $title = get_post_meta($product_id, 'highlights_' . $i . '_title', true);
        
        if(!empty($icon_id) && !empty($title)) {
            echo '<div class="highlights-item">';
            
            // Display icon as image
            echo '<div class="highlights-icon">';
            
            // Get image URL from attachment ID
            $icon_url = wp_get_attachment_url($icon_id);
            
            if($icon_url) {
                echo '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($title) . '">';
            }
            
            echo '</div>';
            
            // Display title
            echo '<div class="highlights-title">' . esc_html($title) . '</div>';
            
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('highlights_info', 'display_highlights_info_shortcode');







/**
 * Shortcode to display tour question answer custom fields
 * Usage: [backroad_counter_clockwise_question_answer]
 */
function display_backroad_counter_clockwise_question_answer_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'backroad_counter_clockwise_question_answer'
    );
    
    $product_id = $atts['product_id'];
    
    // Get tour short info repeater field count
    $repeater_count = get_post_meta($product_id, 'backroad_counter_clockwise_question_answer', true);
    
    // If no data found, return empty
    if(empty($repeater_count) || !is_numeric($repeater_count)) {
        return '<div>No information available</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for the table-like layout
    echo '<style>
        .tour-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .tour-info-row {
            display: flex;
            border-bottom: 1px solid #e5e5e5;
            padding: 20px 0;
        }
        .tour-info-question {
            flex: 0 0 30%;
            font-weight: 600;
            color: #333;
            padding-right: 15px;
        }
        .tour-info-answer {
            flex: 0 0 70%;
            color: #666;
        }
        .tour-info-question h4 {
            margin: 0;
            font-size: 16px;
        }
        .tour-info-answer p {
            margin: 0;
        }
		
		
		/* Responsive styles */
        @media (max-width: 768px) {
            .tour-info-row {
                flex-direction: column;
            }
            .tour-info-question, .tour-info-answer {
                flex: 0 0 100%;
                padding-right: 0;
            }
            .tour-info-question {
                margin-bottom: 10px;
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="tour-info-table">';
    
    // Loop through repeater items
    for($i = 0; $i < $repeater_count; $i++) {
        $question = get_post_meta($product_id, 'backroad_counter_clockwise_question_answer_' . $i . '_questions', true);
        $answer = get_post_meta($product_id, 'backroad_counter_clockwise_question_answer_' . $i . '_answer', true);
        
        if(!empty($question) && !empty($answer)) {
            echo '<div class="tour-info-row">';
            
            // Display question
            echo '<div class="tour-info-question"><h4>'. esc_html($question) . '</h4></div>';
            
            // Display answer
            echo '<div class="tour-info-answer"><p>' . wp_kses_post($answer) . '</p></div>';
            
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('backroad_counter_clockwise_question_answer', 'display_backroad_counter_clockwise_question_answer_shortcode');










/**
 * Shortcode to display tour question answer custom fields as an accordion
 * Usage: [faqs_shortcode]
 */
function display_faqs_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(),
        ),
        $atts,
        'faqs_shortcode'
    );
    
    $product_id = $atts['product_id'];
    
    // Get tour short info repeater field count
    $repeater_count = get_post_meta($product_id, 'faqs', true);
    
    // If no data found, return empty
    if(empty($repeater_count) || !is_numeric($repeater_count)) {
        return '<div>No information available</div>';
    }
    
    // Generate a unique ID for this accordion instance
    $accordion_id = 'faq-accordion-' . uniqid();
    
    // Start output buffer
    ob_start();
    
    // CSS styles for accordion
    echo '<style>
        .faq-accordion {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            font-family: inherit;
        }
        
        .faq-item {
            margin-bottom: 10px;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            overflow: hidden;
            background-color: #fff;
        }
        
        .faq-question {
            position: relative;
            padding: 20px 50px 20px 20px;
            cursor: pointer;
            font-weight: 600;
            background-color: #f9f9f9;
            transition: background-color 0.3s;
        }
        
        .faq-question h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .faq-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .faq-toggle:before,
        .faq-toggle:after {
            content: "";
            position: absolute;
            background-color: #333;
            transition: transform 0.3s ease;
        }
        
        .faq-toggle:before {
            width: 2px;
            height: 12px;
            transform: rotate(90deg);
        }
        
        .faq-toggle:after {
            width: 12px;
            height: 2px;
        }
        
        .faq-item.active .faq-toggle:before {
            transform: rotate(0);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            padding: 0 20px;
            transition: max-height 0.5s ease, padding 0.5s ease;
        }
        
        .faq-item.active .faq-answer {
            max-height: 1000px; /* Arbitrary large value */
            padding: 20px;
        }
        
        .faq-answer p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }
        
        /* Hover state */
        .faq-question:hover {
            background-color: #f0f0f0;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .faq-question {
                padding: 15px 40px 15px 15px;
            }
            
            .faq-toggle {
                right: 15px;
            }
            
            .faq-answer {
                padding: 0 15px;
            }
            
            .faq-item.active .faq-answer {
                padding: 15px;
            }
        }
    </style>';
    
    // HTML output
    echo '<div id="' . esc_attr($accordion_id) . '" class="faq-accordion">';
    
    // Loop through repeater items
    for($i = 0; $i < $repeater_count; $i++) {
        $question = get_post_meta($product_id, 'faqs_' . $i . '_questions', true);
        $answer = get_post_meta($product_id, 'faqs_' . $i . '_answers', true);
		
        
        if(!empty($question) && !empty($answer)) {
            // First item is active by default
            $active_class = ($i === 0) ? 'active' : '';
            
            echo '<div class="faq-item ' . $active_class . '">';
            
            // Display question with toggle button
            echo '<div class="faq-question">';
            echo '<h4>' . esc_html($question) . '</h4>';
            echo '<span class="faq-toggle"></span>';
            echo '</div>';
            
            // Display answer (visible only when active)
            echo '<div class="faq-answer">';
            echo '<p>' . wp_kses_post($answer) . '</p>';
            echo '</div>';
            
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // JavaScript for accordion functionality
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const accordion = document.getElementById("' . $accordion_id . '");
        const questions = accordion.querySelectorAll(".faq-question");
        
        questions.forEach(question => {
            question.addEventListener("click", function() {
                const item = this.parentNode;
                const isActive = item.classList.contains("active");
                
                // Close all items
                const allItems = accordion.querySelectorAll(".faq-item");
                allItems.forEach(faqItem => {
                    faqItem.classList.remove("active");
                });
                
                // If the clicked item wasn\'t active, open it
                if (!isActive) {
                    item.classList.add("active");
                }
            });
        });
    });
    </script>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('faqs_shortcode', 'display_faqs_shortcode');