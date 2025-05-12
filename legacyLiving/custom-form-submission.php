<?php

/**
 * Property Submission Form Shortcode
 * Creates a form for logged-in users to submit property listings
 */
function property_submission_form_shortcode() {
    // Redirect if not logged in
    if (!is_user_logged_in()) {
        wp_redirect('/properties/');
        exit;
    }

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_submission_nonce']) && wp_verify_nonce($_POST['property_submission_nonce'], 'submit_property')) {
        // Basic Fields
        $post_title = sanitize_text_field($_POST['post_title']);
        $post_description = wp_kses_post($_POST['post_description']);
        $excerpt = sanitize_textarea_field($_POST['excerpt']);
        $featured_image_id = intval($_POST['property_thumbnail']);
        
        // Property Details
        $starting_price = floatval($_POST['starting_price']);
        $price = floatval($_POST['price']);
        $location = sanitize_text_field($_POST['location']);
        
        // Media Fields
        $image_gallery = array_filter(array_map('intval', explode(',', sanitize_text_field($_POST['image_gallery']))));
        $video_id = intval($_POST['video']);
        $property_brochure_id = intval($_POST['property_brochure']);
        
        // Floor Plan Group
        $floor_plan_description = sanitize_textarea_field($_POST['description_about_floor']);
        $floor_plan_bedroom = intval($_POST['bedroom']);
        $floor_plan_bathroom = intval($_POST['bathroom']);
        $floor_plan_living_area = intval($_POST['living_area']);
        $floor_plan_image_id = intval($_POST['floor_plan_image']);
        
        // Create the post
        $post_id = wp_insert_post([
            'post_title'   => $post_title,
            'post_content' => $post_description,
            'post_excerpt' => $excerpt,
            'post_type'    => 'property',
            'post_status'  => 'pending', // Set to pending for admin review
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Set featured image
            if ($featured_image_id) {
                set_post_thumbnail($post_id, $featured_image_id);
            }

            // Update basic fields
            update_post_meta($post_id, 'starting_price', $starting_price);
            update_post_meta($post_id, 'price', $price);
            update_post_meta($post_id, 'location', $location);
            update_post_meta($post_id, 'video', $video_id);
            update_post_meta($post_id, 'property_brochure', $property_brochure_id);
            
            // Update image gallery
            if (!empty($image_gallery)) {
                update_post_meta($post_id, 'image_gallery', $image_gallery);
            }
            
            // Floor Plan Group
            $floor_plan = array(
                'description_about_floor' => $floor_plan_description,
                'bedroom' => $floor_plan_bedroom,
                'bathroom' => $floor_plan_bathroom,
                'living_area' => $floor_plan_living_area,
                'floor_plan_image' => $floor_plan_image_id
            );
            update_post_meta($post_id, 'floor_plan', $floor_plan);
            
            // Handle Overview Repeater
            $overview_count = intval($_POST['overview_count']);
            update_post_meta($post_id, 'overview', $overview_count);
            
            for ($i = 0; $i < $overview_count; $i++) {
                if (isset($_POST['overview_' . $i . '_icon']) && 
                    isset($_POST['overview_' . $i . '_title']) && 
                    isset($_POST['overview_' . $i . '_value'])) {
                    
                    update_post_meta($post_id, 'overview_' . $i . '_icon', intval($_POST['overview_' . $i . '_icon']));
                    update_post_meta($post_id, 'overview_' . $i . '_title', sanitize_text_field($_POST['overview_' . $i . '_title']));
                    update_post_meta($post_id, 'overview_' . $i . '_value', sanitize_text_field($_POST['overview_' . $i . '_value']));
                }
            }
            
            // Handle Property Details Repeater
            $property_details_count = intval($_POST['property_details_count']);
            update_post_meta($post_id, 'property_details', $property_details_count);
            
            for ($i = 0; $i < $property_details_count; $i++) {
                if (isset($_POST['property_details_' . $i . '_title']) && 
                    isset($_POST['property_details_' . $i . '_value'])) {
                    
                    update_post_meta($post_id, 'property_details_' . $i . '_title', sanitize_text_field($_POST['property_details_' . $i . '_title']));
                    update_post_meta($post_id, 'property_details_' . $i . '_value', sanitize_text_field($_POST['property_details_' . $i . '_value']));
                }
            }
            
            // Handle Amenities Repeater
            $amenities_count = intval($_POST['amenities_count']);
            update_post_meta($post_id, 'amenities', $amenities_count);
            
            for ($i = 0; $i < $amenities_count; $i++) {
                if (isset($_POST['amenities_' . $i . '_image']) && 
                    isset($_POST['amenities_' . $i . '_title'])) {
                    
                    update_post_meta($post_id, 'amenities_' . $i . '_image', intval($_POST['amenities_' . $i . '_image']));
                    update_post_meta($post_id, 'amenities_' . $i . '_title', sanitize_text_field($_POST['amenities_' . $i . '_title']));
                }
            }

            // Assign selected taxonomy terms to the post
            $taxonomies = array(
                'amenitie' => 'amenities',
                'area' => 'area',
                'attraction' => 'attractions',
                'property-category' => 'categories',
                'property-location' => 'location',
                'property-type' => 'property_type',
                'residential-community' => 'residential_community',
                'custom-tag' => 'tags'
            );
            
            foreach ($taxonomies as $taxonomy => $field_name) {
                if (!empty($_POST[$field_name]) && is_array($_POST[$field_name])) {
                    $term_ids = array_map('intval', $_POST[$field_name]);
                    wp_set_object_terms($post_id, $term_ids, $taxonomy);
                }
            }

            // Redirect to success page or show message
            $redirect_url = add_query_arg('property_submitted', 'success', get_permalink());
            wp_redirect($redirect_url);
            exit;
        }
    }

    // CSS for the form styling
    $form_css = '<style>
    
    .property-form {
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
    color: #333;
    background-color: #f9fafb;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.group-section {
    background: #fff;
    border: none;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}

.group-section:hover {
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.section-head {
    font-size: 22px;
    font-weight: 600;
    margin-top: 0;
    margin-bottom: 25px;
    color: #2c3e50;
    border-bottom: 2px solid #f0f4f8;
    padding-bottom: 15px;
    position: relative;
}

.section-head::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 80px;
    height: 2px;
    background: linear-gradient(to right, #3498db, #2196F3);
}

.info-p {
    background-color: #e1f5fe;
    padding: 12px 15px;
    border-radius: 8px;
    color: #0277bd;
    font-size: 14px;
    margin-bottom: 20px;
    border-left: 4px solid #0288d1;
}

.property-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #455a64;
    font-size: 15px;
}

.property-form input[type="text"],
.property-form input[type="number"],
.property-form input[type="url"],
.property-form textarea,
.property-form select {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 15px;
    background-color: #fff;
    transition: all 0.3s;
    color: #37474f;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
}

.property-form input[type="text"]:focus,
.property-form input[type="number"]:focus,
.property-form input[type="url"]:focus,
.property-form textarea:focus,
.property-form select:focus {
    border-color: #2196F3;
    outline: none;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.double-section {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.double-section p {
    flex: 1 0 calc(50% - 25px);
    min-width: 250px;
}

.featured-image-uploader {
    border: 2px dashed #bdbdbd;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    margin-bottom: 20px;
    background: #f5f7fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.featured-image-uploader:hover {
    border-color: #2196F3;
    background: #f0f7ff;
}

.drag-drop-msg {
    font-size: 28px;
    color: #90a4ae;
    margin-bottom: 15px;
}

.drag-drop-msg i {
    display: block;
    margin-bottom: 10px;
    color: #64b5f6;
}

.repeater-container {
    margin-bottom: 25px;
}

.repeater-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 25px;
    margin-bottom: 15px;
    border-radius: 10px;
    position: relative;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}

.repeater-item:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.07);
}

.remove-item {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ef5350;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    cursor: pointer;
    font-size: 16px;
    z-index: 5;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.remove-item:hover {
    background: #e53935;
    transform: scale(1.05);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.add-item {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
    font-weight: 500;
    font-size: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    display: flex;
    align-items: center;
}

.add-item:hover {
    background: #43A047;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    transform: translateY(-2px);
}

.add-item:before {
    content: '+';
    font-size: 18px;
    margin-right: 8px;
}

.image-preview {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.image-preview img {
    max-width: 120px;
    max-height: 120px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.image-preview img:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

button[type="submit"] {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    color: white;
    border: none;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    width: 100%;
    max-width: 300px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

button[type="submit"]:hover {
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    box-shadow: 0 6px 18px rgba(33, 150, 243, 0.4);
    transform: translateY(-2px);
}

.select-media-btn {
    background: #eceff1;
    border: none;
    padding: 10px 18px;
    margin-top: 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #455a64;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.select-media-btn:hover {
    background: #cfd8dc;
    color: #263238;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}
.tag-input-container {
    position: relative;
    margin-bottom: 15px;
    width: 100%;
}

.tag-input {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    min-height: 50px;
    margin-bottom: 10px;
    align-items: center;
}

.tag-input:empty {
    padding: 0;
    min-height: 0;
    border: none;
    margin-bottom: 0;
}

.tag-item {
    background: #e3f2fd;
    border-radius: 20px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    color: #1976D2;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    margin-right: 5px;
    margin-bottom: 5px;
}

.tag-item:hover {
    background: #bbdefb;
}

.remove-tag {
    margin-left: 8px;
    color: #1976D2;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    line-height: 1;
}

.remove-tag:hover {
    color: #1565C0;
}

.tag-text-input {
    width: 100% !important;
    padding: 14px 16px !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 8px !important;
    box-sizing: border-box !important;
    font-size: 15px !important;
    background-color: #fff !important;
    transition: all 0.3s !important;
    color: #37474f !important;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02) !important;
    margin: 0 !important;
}

.tag-text-input:focus {
    border-color: #2196F3 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1) !important;
}

.tag-suggestions {
    position: absolute;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    z-index: 10000;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    display: none;
}

.tag-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f5f5f5;
}

.tag-suggestion-item:hover {
    background: #e3f2fd;
}

.tag-suggestion-item.new-tag {
    font-style: italic;
    color: #00796B;
    background: #e8f5e9;
}

.tag-suggestion-item.new-tag:hover {
    background: #c8e6c9;
}
.submission-message {
    padding: 20px !important;
    margin-bottom: 30px !important;
    border-radius: 10px !important;
    text-align: center;
}

.submission-message.success {
    background: #e8f5e9 !important;
    border: none !important;
    color: #2e7d32 !important;
    box-shadow: 0 3px 10px rgba(46, 125, 50, 0.1) !important;
}

.submission-message p {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

.file-preview {
    padding: 10px 15px;
    background: #eceff1;
    border-radius: 6px;
    margin-top: 10px;
    font-size: 14px;
    color: #455a64;
    display: inline-block;
}

@media (max-width: 768px) {
    .property-form {
        padding: 1.5rem;
    }
    
    .group-section {
        padding: 20px;
    }
    
    .double-section p {
        flex: 1 0 100%;
    }
    
    button[type="submit"] {
        width: 100%;
    }
}
    
    </style>';

    // Start output buffer
    ob_start();
    
    // Show success message if property was submitted
    if (isset($_GET['property_submitted']) && $_GET['property_submitted'] === 'success') {
        echo '<div class="submission-message success" style="background: #e7f7e7; border: 1px solid #c3e6c3; color: #2e7d32; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p>Property submitted successfully! It will be reviewed by an administrator.</p>
        </div>';
    }
    
    // Output the CSS
    echo $form_css;
    
    // Form HTML
    ?>
    <form method="post" enctype="multipart/form-data" class="property-form">
        <!-- General Information Section -->
        <fieldset class="group-section">
            <p class="info-p">These fields are mandatory: Title, Property Thumbnail, Description</p>
            <h3 class="section-head">General Information</h3>
            <p>
                <label for="post_title">Title</label>
                <input type="text" name="post_title" id="post_title" required>
            </p>
            <p>
                <label for="post_description">Description</label>
                <textarea name="post_description" id="post_description" rows="6" required></textarea>
            </p>
            <p>
                <label for="excerpt">Excerpt</label>
                <textarea name="excerpt" id="excerpt" rows="3"></textarea>
            </p>
            <p>
                <label for="property_thumbnail">Property Thumbnail</label>
                <div class="featured-image-uploader">
                    <div class="drag-drop-msg"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div id="thumbnail_preview">Drag and drop an image here or click to select</div>
                    <button type="button" class="select-media-btn" id="select_thumbnail_button">Select Media</button>
                    <input type="hidden" name="property_thumbnail" id="property_thumbnail">
                </div>
            </p>
            
        </fieldset>


        <fieldset class="group-section">
            
            <!-- Taxonomies Section -->
            <div class="double-section">
                <p>
                    <label for="property_type">Property Type</label>
                    <div class="tag-input-container">
                        <div class="tag-input" id="property_type_container"></div>
                        <input type="text" id="property_type_input" placeholder="Type to add property types" class="tag-text-input">
                        <div class="tag-suggestions" id="property_type_suggestions"></div>
                    </div>
                </p>
                <p>
                    <label for="area">Area</label>
                    <div class="tag-input-container">
                        <div class="tag-input" id="area_container"></div>
                        <input type="text" id="area_input" placeholder="Type to add areas" class="tag-text-input">
                        <div class="tag-suggestions" id="area_suggestions"></div>
                    </div>
                </p>
            </div>
            
            <div class="double-section">
                <p>
                    <label for="property_location">Location</label>
                    <div class="tag-input-container">
                        <div class="tag-input" id="property_location_container"></div>
                        <input type="text" id="property_location_input" placeholder="Type to add locations" class="tag-text-input">
                        <div class="tag-suggestions" id="property_location_suggestions"></div>
                    </div>
                </p>
                <p>
                    <label for="residential_community">Residential Community</label>
                    <div class="tag-input-container">
                        <div class="tag-input" id="residential_community_container"></div>
                        <input type="text" id="residential_community_input" placeholder="Type to add communities" class="tag-text-input">
                        <div class="tag-suggestions" id="residential_community_suggestions"></div>
                    </div>
                </p>
            </div>
        </fieldset>
        
        <!-- Overview Section (Repeater) -->
        <fieldset class="group-section">
            <h3 class="section-head">Property Overview</h3>
            <div id="overview_container" class="repeater-container">
                <!-- Repeater items will be added dynamically -->
            </div>
            <button type="button" class="add-item" id="add_overview">Add Overview Item</button>
            <input type="hidden" name="overview_count" id="overview_count" value="0">
        </fieldset>
        
        <!-- Property Details Section (Repeater) -->
        <fieldset class="group-section">
            <h3 class="section-head">Property Details</h3>
            <div id="property_details_container" class="repeater-container">
                <!-- Repeater items will be added dynamically -->
            </div>
            <button type="button" class="add-item" id="add_property_detail">Add Property Detail</button>
            <input type="hidden" name="property_details_count" id="property_details_count" value="0">
        </fieldset>
        
        <!-- Amenities Section (Repeater) -->
        <fieldset class="group-section">
            <h3 class="section-head">Amenities</h3>
            <div id="amenities_container" class="repeater-container">
                <!-- Repeater items will be added dynamically -->
            </div>
            <button type="button" class="add-item" id="add_amenity">Add Amenity</button>
            <input type="hidden" name="amenities_count" id="amenities_count" value="0">
            
            <p style="margin-top: 20px;">
                <label for="amenities">Amenities Tags</label>
                <div class="tag-input-container">
                    <div class="tag-input" id="amenities_container_tags"></div>
                    <input type="text" id="amenities_input" placeholder="Type to add amenities" class="tag-text-input">
                    <div class="tag-suggestions" id="amenities_suggestions"></div>
                </div>
            </p>
        </fieldset>
        
        <!-- Pricing Section -->
        <fieldset class="group-section">
            <h3 class="section-head">Pricing</h3>
            <div class="double-section">
                <p>
                    <label for="starting_price">Starting Price</label>
                    <input type="number" name="starting_price" id="starting_price" min="0" step="0.01">
                </p>
                <p>
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" min="0" step="0.01">
                </p>
            </div>
        </fieldset>
        
        <!-- Floor Plan Section -->
        <fieldset class="group-section">
            <h3 class="section-head">Floor Plan</h3>
            <p>
                <label for="description_about_floor">Description about Floor</label>
                <textarea name="description_about_floor" id="description_about_floor" rows="4"></textarea>
            </p>
            
            <div class="double-section">
                <p>
                    <label for="bedroom">Bedroom</label>
                    <input type="number" name="bedroom" id="bedroom" min="0">
                </p>
                <p>
                    <label for="bathroom">Bathroom</label>
                    <input type="number" name="bathroom" id="bathroom" min="0">
                </p>
            </div>
            
            <p>
                <label for="living_area">Living Area</label>
                <input type="number" name="living_area" id="living_area" min="0">
            </p>
            
            <p>
                <label for="floor_plan_image">Floor Plan Image</label>
                <div class="featured-image-uploader">
                    <div class="drag-drop-msg"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div id="floor_plan_preview">Drag and drop an image here or click to select</div>
                    <button type="button" class="select-media-btn" id="select_floor_plan_button">Select Media</button>
                    <input type="hidden" name="floor_plan_image" id="floor_plan_image">
                </div>
            </p>
        </fieldset>
        
        <!-- Location Section -->
        <fieldset class="group-section">
            <h3 class="section-head">Location</h3>
            <p>
                <label for="location">Location</label>
                <input type="text" name="location" id="location">
            </p>
            
            <p>
                <label for="attractions">Nearby Attractions</label>
                <div class="tag-input-container">
                    <div class="tag-input" id="attractions_container"></div>
                    <input type="text" id="attractions_input" placeholder="Type to add attractions" class="tag-text-input">
                    <div class="tag-suggestions" id="attractions_suggestions"></div>
                </div>
            </p>
        </fieldset>
        
        <!-- Media Section -->
        <fieldset class="group-section">
            <h3 class="section-head">Media</h3>
            <p>
                <label for="image_gallery">Image Gallery</label>
                <div class="featured-image-uploader">
                    <div class="drag-drop-msg"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div id="gallery_preview">Drag and drop images here or click to select</div>
                    <button type="button" class="select-media-btn" id="select_gallery_button">Select Media</button>
                    <input type="hidden" name="image_gallery" id="image_gallery">
                </div>
                <div id="gallery_images" class="image-preview"></div>
            </p>
            
            <p>
                <label for="video">Video</label>
                <div class="featured-image-uploader">
                    <div class="drag-drop-msg"><i class="fas fa-video"></i></div>
                    <div id="video_preview">Drag and drop a video here or click to select</div>
                    <button type="button" class="select-media-btn" id="select_video_button">Select Media</button>
                    <input type="hidden" name="video" id="video">
                </div>
            </p>
            
            <p>
                <label for="property_brochure">Property Brochure</label>
                <div class="featured-image-uploader">
                    <div class="drag-drop-msg"><i class="fas fa-file-pdf"></i></div>
                    <div id="brochure_preview">Drag and drop a brochure here or click to select</div>
                    <button type="button" class="select-media-btn" id="select_brochure_button">Select Media</button>
                    <input type="hidden" name="property_brochure" id="property_brochure">
                </div>
            </p>
        </fieldset>
        
        <!-- Tags Section -->
        <fieldset class="group-section">
            <h3 class="section-head">Tags</h3>
            <p>
                <label for="tags">Property Tags</label>
                <div class="tag-input-container">
                    <div class="tag-input" id="tags_container"></div>
                    <input type="text" id="tags_input" placeholder="Type to add tags" class="tag-text-input">
                    <div class="tag-suggestions" id="tags_suggestions"></div>
                </div>
            </p>
            
            <p>
                <label for="categories">Categories</label>
                <div class="tag-input-container">
                    <div class="tag-input" id="categories_container"></div>
                    <input type="text" id="categories_input" placeholder="Type to add categories" class="tag-text-input">
                    <div class="tag-suggestions" id="categories_suggestions"></div>
                </div>
            </p>
        </fieldset>
        
        <!-- Submit Button -->
        <fieldset style="border: none;">
            <p>
                <?php wp_nonce_field('submit_property', 'property_submission_nonce'); ?>
                <button id="property-submit" type="submit">Submit Property</button>
            </p>
        </fieldset>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
    // Media uploader functions
    function initMediaUploader(buttonId, inputId, previewId, multiple = false) {
        $('#' + buttonId).on('click', function(e) {
            e.preventDefault();
            
            var mediaUploader;
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Select Media',
                button: {
                    text: 'Use This Media'
                },
                multiple: multiple
            });
            
            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection');
                
                if (multiple) {
                    var attachmentIds = [];
                    var previewHtml = '';
                    
                    attachments.map(function(attachment) {
                        attachment = attachment.toJSON();
                        attachmentIds.push(attachment.id);
                        
                        if (attachment.type === 'image') {
                            previewHtml += '<img src="' + attachment.url + '" alt="Preview">';
                        } else {
                            previewHtml += '<div class="file-preview">' + attachment.filename + '</div>';
                        }
                    });
                    
                    $('#' + inputId).val(attachmentIds.join(','));
                    $('#' + previewId).html(previewHtml);
                } else {
                    var attachment = attachments.first().toJSON();
                    
                    $('#' + inputId).val(attachment.id);
                    
                    if (attachment.type === 'image') {
                        $('#' + previewId).html('<img src="' + attachment.url + '" alt="Preview" style="max-width: 100%; max-height: 200px;">');
                    } else {
                        $('#' + previewId).html('<div class="file-preview">' + attachment.filename + '</div>');
                    }
                }
            });
            
            mediaUploader.open();
        });
    }
    
    // Initialize media uploaders
    initMediaUploader('select_thumbnail_button', 'property_thumbnail', 'thumbnail_preview');
    initMediaUploader('select_floor_plan_button', 'floor_plan_image', 'floor_plan_preview');
    initMediaUploader('select_gallery_button', 'image_gallery', 'gallery_preview', true);
    initMediaUploader('select_video_button', 'video', 'video_preview');
    initMediaUploader('select_brochure_button', 'property_brochure', 'brochure_preview');
    
    // Repeater functions
    function addOverviewItem(icon = '', title = '', value = '') {
        var count = parseInt($('#overview_count').val());
        
        var itemHtml = `
            <div class="repeater-item" data-index="${count}">
                <button type="button" class="remove-item remove-overview">&times;</button>
                <div class="double-section">
                    <p>
                        <label for="overview_${count}_title">Title</label>
                        <input type="text" name="overview_${count}_title" id="overview_${count}_title" value="${title}">
                    </p>
                    <p>
                        <label for="overview_${count}_value">Value</label>
                        <input type="text" name="overview_${count}_value" id="overview_${count}_value" value="${value}">
                    </p>
                </div>
                <p>
                    <label for="overview_${count}_icon">Icon</label>
                    <div class="featured-image-uploader">
                        <div class="drag-drop-msg"><i class="fas fa-image"></i></div>
                        <div id="overview_${count}_icon_preview">Select an icon</div>
                        <button type="button" class="select-media-btn select-icon-btn" data-index="${count}">Select Icon</button>
                        <input type="hidden" name="overview_${count}_icon" id="overview_${count}_icon" value="${icon}">
                    </div>
                </p>
            </div>
        `;
        
        $('#overview_container').append(itemHtml);
        $('#overview_count').val(count + 1);
        
        // Initialize the media uploader for this icon
        $('.select-icon-btn[data-index="' + count + '"]').on('click', function() {
            var index = $(this).data('index');
            
            var mediaUploader = wp.media({
                title: 'Select Icon',
                button: {
                    text: 'Use This Icon'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $('#overview_' + index + '_icon').val(attachment.id);
                
                if (attachment.type === 'image') {
                    $('#overview_' + index + '_icon_preview').html('<img src="' + attachment.url + '" alt="Icon Preview" style="max-width: 100px; max-height: 100px;">');
                }
            });
            
            mediaUploader.open();
        });
    }
    
    function addPropertyDetailItem(title = '', value = '') {
        var count = parseInt($('#property_details_count').val());
        
        var itemHtml = `
            <div class="repeater-item" data-index="${count}">
                <button type="button" class="remove-item remove-property-detail">&times;</button>
                <div class="double-section">
                    <p>
                        <label for="property_details_${count}_title">Title</label>
                        <input type="text" name="property_details_${count}_title" id="property_details_${count}_title" value="${title}">
                    </p>
                    <p>
                        <label for="property_details_${count}_value">Value</label>
                        <input type="text" name="property_details_${count}_value" id="property_details_${count}_value" value="${value}">
                    </p>
                </div>
            </div>
        `;
        
        $('#property_details_container').append(itemHtml);
        $('#property_details_count').val(count + 1);
    }
    
    function addAmenityItem(image = '', title = '') {
        var count = parseInt($('#amenities_count').val());
        
        var itemHtml = `
            <div class="repeater-item" data-index="${count}">
                <button type="button" class="remove-item remove-amenity">&times;</button>
                <p>
                    <label for="amenities_${count}_title">Title</label>
                    <input type="text" name="amenities_${count}_title" id="amenities_${count}_title" value="${title}">
                </p>
                <p>
                    <label for="amenities_${count}_image">Image</label>
                    <div class="featured-image-uploader">
                        <div class="drag-drop-msg"><i class="fas fa-image"></i></div>
                        <div id="amenities_${count}_image_preview">Select an image</div>
                        <button type="button" class="select-media-btn select-amenity-img-btn" data-index="${count}">Select Image</button>
                        <input type="hidden" name="amenities_${count}_image" id="amenities_${count}_image" value="${image}">
                    </div>
                </p>
            </div>
        `;
        
        $('#amenities_container').append(itemHtml);
        $('#amenities_count').val(count + 1);
        
        // Initialize the media uploader for this amenity image
        $('.select-amenity-img-btn[data-index="' + count + '"]').on('click', function() {
            var index = $(this).data('index');
            
            var mediaUploader = wp.media({
                title: 'Select Amenity Image',
                button: {
                    text: 'Use This Image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $('#amenities_' + index + '_image').val(attachment.id);
                
                if (attachment.type === 'image') {
                    $('#amenities_' + index + '_image_preview').html('<img src="' + attachment.url + '" alt="Amenity Preview" style="max-width: 100px; max-height: 100px;">');
                }
            });
            
            mediaUploader.open();
        });
    }
    
    // Add repeater item buttons
    $('#add_overview').on('click', function() {
        addOverviewItem();
    });
    
    $('#add_property_detail').on('click', function() {
        addPropertyDetailItem();
    });
    
    $('#add_amenity').on('click', function() {
        addAmenityItem();
    });
    
    // Remove repeater item buttons
    $(document).on('click', '.remove-overview', function() {
        $(this).closest('.repeater-item').remove();
    });
    
    $(document).on('click', '.remove-property-detail', function() {
        $(this).closest('.repeater-item').remove();
    });
    
    $(document).on('click', '.remove-amenity', function() {
        $(this).closest('.repeater-item').remove();
    });
    
    // Add some initial items
    addOverviewItem();
    addPropertyDetailItem();
    addAmenityItem();
    
    // Tag Input System
    function initTagSystem(inputId, containerId, suggestionsId, taxonomy) {
        var selectedTags = [];
        
        // Function to render the selected tags
        function renderTags() {
            var html = '';
            if (selectedTags.length > 0) {
                selectedTags.forEach(function(tag, index) {
                    html += `<div class="tag-item" data-id="${tag.id}">
                        ${tag.name}
                        <span class="remove-tag" data-index="${index}">&times;</span>
                        <input type="hidden" name="${taxonomy}[]" value="${tag.id}">
                    </div>`;
                });
            }
            $('#' + containerId).html(html);
        }
        
        // Input keyup event to search for tags
        $('#' + inputId).on('keyup focus', function() {
            var searchTerm = $(this).val();
            
            if (searchTerm.length < 2) {
                $('#' + suggestionsId).hide();
                return;
            }
            
            // AJAX request to search for tags
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'search_taxonomy_terms',
                    taxonomy: taxonomy,
                    search: searchTerm,
                    nonce: property_submission_vars.nonce
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var suggestionsHtml = '';
                        response.data.forEach(function(term) {
                            // Check if this term is already selected
                            var isSelected = selectedTags.some(function(tag) {
                                return parseInt(tag.id) === parseInt(term.id);
                            });
                            
                            if (!isSelected) {
                                suggestionsHtml += `<div class="tag-suggestion-item" data-id="${term.id}" data-name="${term.name}">${term.name}</div>`;
                            }
                        });
                        
                        if (suggestionsHtml) {
                            $('#' + suggestionsId).html(suggestionsHtml).show();
                        } else {
                            $('#' + suggestionsId).html(`<div class="tag-suggestion-item new-tag" data-name="${searchTerm}">Add "${searchTerm}" as new term</div>`).show();
                        }
                    } else {
                        $('#' + suggestionsId).html(`<div class="tag-suggestion-item new-tag" data-name="${searchTerm}">Add "${searchTerm}" as new term</div>`).show();
                    }
                },
                error: function() {
                    console.log('Error searching for taxonomy terms');
                }
            });
        });
        
        // Click event for selecting a tag suggestion
        $(document).on('click', '#' + suggestionsId + ' .tag-suggestion-item', function() {
            var tagId = $(this).data('id');
            var tagName = $(this).data('name');
            
            // If it's a new tag
            if ($(this).hasClass('new-tag')) {
                // AJAX request to create a new term
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'create_taxonomy_term',
                        taxonomy: taxonomy,
                        name: tagName,
                        nonce: property_submission_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            selectedTags.push({
                                id: response.data.term_id,
                                name: tagName
                            });
                            renderTags();
                        } else {
                            console.log('Error creating new term:', response.data);
                        }
                    },
                    error: function() {
                        console.log('Error creating new taxonomy term');
                    }
                });
            } else {
                selectedTags.push({
                    id: tagId,
                    name: tagName
                });
                renderTags();
            }
            
            $('#' + inputId).val('');
            $('#' + suggestionsId).hide();
        });
        
        // Click event for removing a tag
        $(document).on('click', '#' + containerId + ' .remove-tag', function() {
            var index = $(this).data('index');
            selectedTags.splice(index, 1);
            renderTags();
        });
        
        // Close suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#' + inputId).length && !$(e.target).closest('#' + suggestionsId).length) {
                $('#' + suggestionsId).hide();
            }
        });
        
        // Initialize with existing values if any
        if (window.property_submission_vars && window.property_submission_vars[taxonomy]) {
            selectedTags = window.property_submission_vars[taxonomy].map(function(term) {
                return {
                    id: term.id,
                    name: term.name
                };
            });
            renderTags();
        }
    }
    
    // Initialize tag systems for each taxonomy
    initTagSystem('property_type_input', 'property_type_container', 'property_type_suggestions', 'property_type');
    initTagSystem('area_input', 'area_container', 'area_suggestions', 'area');
    initTagSystem('property_location_input', 'property_location_container', 'property_location_suggestions', 'property_location');
    initTagSystem('residential_community_input', 'residential_community_container', 'residential_community_suggestions', 'residential_community');
    initTagSystem('amenities_input', 'amenities_container_tags', 'amenities_suggestions', 'amenities');
    initTagSystem('attractions_input', 'attractions_container', 'attractions_suggestions', 'attractions');
    initTagSystem('tags_input', 'tags_container', 'tags_suggestions', 'tags');
    initTagSystem('categories_input', 'categories_container', 'categories_suggestions', 'categories');
    
    // Form submission validation
    $('.property-form').on('submit', function(e) {
        // Update hidden counters before submission
        $('#overview_count').val($('#overview_container .repeater-item').length);
        $('#property_details_count').val($('#property_details_container .repeater-item').length);
        $('#amenities_count').val($('#amenities_container .repeater-item').length);
        
        // Validate required fields
        var isValid = true;
        
        if ($('#post_title').val().trim() === '') {
            alert('Title is required');
            isValid = false;
        }
        
        if ($('#post_description').val().trim() === '') {
            alert('Description is required');
            isValid = false;
        }
        
        if ($('#property_thumbnail').val() === '') {
            alert('Property thumbnail is required');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});



// Add this code to your PHP file, before the closing function bracket of property_submission_form_shortcode()

// Localize script with needed variables
wp_localize_script('jquery', 'property_submission_vars', array(
    'nonce' => wp_create_nonce('property_submission_nonce'),
    'ajaxurl' => admin_url('admin-ajax.php'),
    // Pre-populate existing taxonomy terms if editing
    'property_type' => array(), // Add your existing terms here if editing
    'area' => array(),
    'property_location' => array(),
    'residential_community' => array(),
    'amenities' => array(),
    'attractions' => array(),
    'tags' => array(),
    'categories' => array()
));

// Also update your AJAX handlers to verify nonce
add_action('wp_ajax_search_taxonomy_terms', 'search_taxonomy_terms');
function search_taxonomy_terms() {
    // Verify nonce for security
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'property_submission_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $taxonomy = sanitize_text_field($_GET['taxonomy']);
    $search = sanitize_text_field($_GET['search']);
    
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'search' => $search
    ]);
    
    $results = [];
    foreach ($terms as $term) {
        $results[] = [
            'id' => $term->term_id,
            'name' => $term->name
        ];
    }
    
    wp_send_json_success($results);
}

add_action('wp_ajax_create_taxonomy_term', 'create_taxonomy_term');
function create_taxonomy_term() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'property_submission_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $name = sanitize_text_field($_POST['name']);
    
    $result = wp_insert_term(
        $name,
        $taxonomy
    );
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(['term_id' => $result['term_id']]);
    }
}



    return ob_get_clean();
}
add_shortcode('property_submission_form', 'property_submission_form_shortcode');