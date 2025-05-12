<!-- Banner dynamic filter -->



// Developer: Antu Paul
// Organization: SEOPage1
// Organization URL: seopage1.net
// Date: 03.24.2025

<?php

// Enqueue inline CSS and JS for the property filter
function property_filter_scripts() {
    // Only enqueue if shortcode is used on the page
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'property_filter')) {
        // Add inline CSS
        $css = '
        /* Property Filter CSS */
        .property-filter-container {
            width: 100%;
            background-image: linear-gradient(280deg, rgba(40, 40, 40, 0.7) 0%, rgba(255, 255, 255, 0.7) 100%);
            border-radius: 10px;
            padding: 30px 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .property-filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        
        .property-filter-item {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        
        .property-filter-dropdown,
        .property-filter-input {
            width: 100%;
            height: 50px;
            padding: 10px 15px;
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            outline: none;
        }
        
        .property-filter-dropdown {
            background-image: url(\'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>\');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }
        
        .property-filter-button {
            margin-left: auto;
        }
        
        #find-properties-btn {
            min-width: 180px;
            height: 50px;
            background-color: #C4A86C;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        #find-properties-btn:hover {
            background-color: #B39659;
        }
        
        .property-filter-dropdown.loading {
            background-image: url(\'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 12a4 4 0 1 1-8 0"></path></svg>\');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .property-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .property-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .property-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .property-content {
            padding: 20px;
    		font-family: Lora;
        }
		.property-content h3 a{
			font-size: 20px;
			color: #0D0D0C !important;
			line-height: 26px;
		}
        
        .property-meta {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            color: #555;
        }
        
        .property-price {
            font-weight: bold;
            color: #0D0D0C;
            margin: 10px 0;
			font-size: 16px;
        }
        
        .property-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: transparent;
            color: white;
            text-decoration: none;			
			border: 1px solid #0D0D0C;
            border-radius: 30px;
            margin-top: 10px;
            transition: background-color 0.3s;
			color: #0D0D0C;
        }
        
        .property-link:hover {
            background-image: linear-gradient(280deg, #847657 0%, #EAD29A 100%);
			color: #000000;
        }
        
        .loading-properties,
        .no-properties-found,
        .error-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #555;
        }
        
        .loading-properties:before {
            content: \'\';
            display: block;
            width: 40px;
            height: 40px;
            border: 4px solid #C4A86C;
            border-top-color: transparent;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }
        
        @media (max-width: 1024px) {
            .property-filter-row {
                flex-direction: row;
                gap: 15px;
            }
            
            .property-filter-item {
                width: 100%;
            }
            
            .property-filter-button {
                width: 100%;
                margin-left: 0;
            }
            
            #find-properties-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .property-grid {
                grid-template-columns: 1fr;
            }
        }
        ';
        
        wp_register_style('property-filter-inline-style', false);
        wp_enqueue_style('property-filter-inline-style');
        wp_add_inline_style('property-filter-inline-style', $css);
        
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        
        // Add inline JavaScript
        $js = '
        jQuery(document).ready(function($) {
            // Handle location dropdown change
            $(\'#location-dropdown\').on(\'change\', function() {
                var locationId = $(this).val();
                
                // Reset sub-locality dropdown
                $(\'#sub-locality-dropdown\').html(\'<option value="">Sub-locality</option>\');
                
                if (locationId) {
                    // Show loading indicator
                    $(\'#sub-locality-dropdown\').addClass(\'loading\');
                    
                    // AJAX call to get sub-localities
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: {
                            action: "get_sub_localities",
                            location_id: locationId,
                            nonce: "' . wp_create_nonce('property_filter_nonce') . '"
                        },
                        success: function(response) {
                            $(\'#sub-locality-dropdown\').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading sub-localities:", error);
                        },
                        complete: function() {
                            // Hide loading indicator
                            $(\'#sub-locality-dropdown\').removeClass(\'loading\');
                        }
                    });
                }
            });
            
            // Handle Find Properties button click
            $(\'#find-properties-btn\').on(\'click\', function() {
                filterProperties();
            });
            
            // Handle map search input keypress
            $(\'#map-search\').on(\'keypress\', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    filterProperties();
                }
            });
            
            // Function to filter properties
            function filterProperties() {
                var locationId = $(\'#location-dropdown\').val();
                var subLocality = $(\'#sub-locality-dropdown\').val();
                var propertyTypeId = $(\'#property-type-dropdown\').val();
                var searchTerm = $(\'#map-search\').val();
                
                // Show loading indicator
                $(\'#property-filter-results\').html(\'<div class="loading-properties">Loading properties...</div>\');
                
                // AJAX call to filter properties
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "filter_properties",
                        location_id: locationId,
                        sub_locality: subLocality,
                        property_type_id: propertyTypeId,
                        search_term: searchTerm,
                        nonce: "' . wp_create_nonce('property_filter_nonce') . '"
                    },
                    success: function(response) {
                        $(\'#property-filter-results\').html(response.html);
                        
                        // Scroll to results
                        $(\'html, body\').animate({
                            scrollTop: $(\'#property-filter-results\').offset().top - 100
                        }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error filtering properties:", error);
                        $(\'#property-filter-results\').html(\'<div class="error-message">Error loading properties. Please try again.</div>\');
                    }
                });
            }
        });
        ';
        
        wp_register_script('property-filter-inline-script', false);
        wp_enqueue_script('property-filter-inline-script');
        wp_add_inline_script('property-filter-inline-script', $js);
    }
}
add_action('wp_enqueue_scripts', 'property_filter_scripts');

// Create the property filter shortcode
function property_filter_shortcode() {
    ob_start();
    ?>
    <div class="property-filter-container">
        <div class="property-filter-row">
            <div class="property-filter-item">
                <select id="location-dropdown" class="property-filter-dropdown">
                    <option value="">Abu Dhabi</option>
                    <?php
                    // Get all terms from property-location taxonomy
                    $locations = get_terms(array(
                        'taxonomy' => 'property-location',
                        'hide_empty' => false,
                    ));
                    
                    if (!empty($locations) && !is_wp_error($locations)) {
                        foreach ($locations as $location) {
                            echo '<option value="' . esc_attr($location->term_id) . '">' . esc_html($location->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="property-filter-item">
                <select id="sub-locality-dropdown" class="property-filter-dropdown">
                    <option value="">Sub-locality</option>
                    <!-- Will be populated via AJAX -->
                </select>
            </div>
            
            <div class="property-filter-item">
                <select id="property-type-dropdown" class="property-filter-dropdown">
                    <option value="">Property Type</option>
                    <?php
                    // Get all terms from property-tag taxonomy
                    $property_types = get_terms(array(
                        'taxonomy' => 'property-tag',
                        'hide_empty' => false,
                    ));
                    
                    if (!empty($property_types) && !is_wp_error($property_types)) {
                        foreach ($property_types as $type) {
                            echo '<option value="' . esc_attr($type->term_id) . '">' . esc_html($type->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="property-filter-item">
                <div class="map-search-container">
                    <input type="text" id="map-search" class="property-filter-input" placeholder="Map Search">
                </div>
            </div>
            
            <div class="property-filter-button">
                <button id="find-properties-btn">Find Properties</button>
            </div>
        </div>
        
        <div id="property-filter-results"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('property_filter', 'property_filter_shortcode');

// AJAX handler for sub-locality dropdown
function get_sub_localities_ajax() {
    // Check nonce for security
    check_ajax_referer('property_filter_nonce', 'nonce');
    
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    $sub_localities = array();
    
    if ($location_id) {
        // Get properties with the selected location
        $args = array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'property-location',
                    'field' => 'term_id',
                    'terms' => $location_id,
                ),
            ),
        );
        
        $properties = new WP_Query($args);
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $sub_locality = get_post_meta(get_the_ID(), 'location', true);
                
                if (!empty($sub_locality) && !in_array($sub_locality, $sub_localities)) {
                    $sub_localities[] = $sub_locality;
                }
            }
        }
        wp_reset_postdata();
    }
    
    // Sort sub-localities alphabetically
    sort($sub_localities);
    
    // Format response
    $response = '<option value="">Sub-locality</option>';
    foreach ($sub_localities as $sub_locality) {
        $response .= '<option value="' . esc_attr($sub_locality) . '">' . esc_html($sub_locality) . '</option>';
    }
    
    echo $response;
    wp_die();
}
add_action('wp_ajax_get_sub_localities', 'get_sub_localities_ajax');
add_action('wp_ajax_nopriv_get_sub_localities', 'get_sub_localities_ajax');

// AJAX handler for property filter
function filter_properties_ajax() {
    // Check nonce for security
    check_ajax_referer('property_filter_nonce', 'nonce');
    
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    $sub_locality = isset($_POST['sub_locality']) ? sanitize_text_field($_POST['sub_locality']) : '';
    $property_type_id = isset($_POST['property_type_id']) ? intval($_POST['property_type_id']) : 0;
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    
    // Build query args
    $args = array(
        'post_type' => 'property',
        'posts_per_page' => 12,
    );
    
    // Tax query for location and property type
    $tax_query = array();
    
    if ($location_id) {
        $tax_query[] = array(
            'taxonomy' => 'property-location',
            'field' => 'term_id',
            'terms' => $location_id,
        );
    }
    
    if ($property_type_id) {
        $tax_query[] = array(
            'taxonomy' => 'property-tag',
            'field' => 'term_id',
            'terms' => $property_type_id,
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    // Meta query for sub-locality
    $meta_query = array();
    
    if (!empty($sub_locality)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $sub_locality,
            'compare' => '=',
        );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    // Search
    if (!empty($search_term)) {
        $args['s'] = $search_term;
    }
    
    // Run query
    $properties = new WP_Query($args);
    
    $response = array(
        'count' => $properties->found_posts,
        'html' => '',
    );
    
    // Build HTML for results
    if ($properties->have_posts()) {
        ob_start();
        echo '<div class="property-grid">';
        while ($properties->have_posts()) {
            $properties->the_post();
            $property_id = get_the_ID();
            $beds = get_post_meta($property_id, 'bed', true);
            $baths = get_post_meta($property_id, 'bath', true);
            $size = get_post_meta($property_id, 'size', true);
            $starting_price = get_post_meta($property_id, 'starting_price', true);
            ?>
            <div class="property-item">
                <div class="property-image">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="property-content">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="property-meta">
                        <?php if ($beds) : ?>
                            <span class="beds"><?php echo esc_html($beds); ?> Beds</span>
                        <?php endif; ?>
                        
                        <?php if ($baths) : ?>
                            <span class="baths"><?php echo esc_html($baths); ?> Baths</span>
                        <?php endif; ?>
                        
                        <?php if ($size) : ?>
                            <span class="size"><?php echo esc_html($size); ?> sqft</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($starting_price) : ?>
                        <div class="property-price">Starting from $<?php echo esc_html($starting_price); ?></div>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="property-link">View Details</a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
        
        $response['html'] = ob_get_clean();
    } else {
        $response['html'] = '<div class="no-properties-found">No properties found matching your criteria.</div>';
    }
    
    wp_send_json($response);
}
add_action('wp_ajax_filter_properties', 'filter_properties_ajax');
add_action('wp_ajax_nopriv_filter_properties', 'filter_properties_ajax');