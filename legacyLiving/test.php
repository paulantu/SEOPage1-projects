<?php



/**
 *  Nearby area Grid Shortcode
 * Displays all Nearby area in a responsive grid layout
 */
function nearby_area_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'exclude_current' => 'yes', // Whether to exclude current area
            'columns' => 4, // Number of columns on desktop
            'limit' => -1, // Limit the number of area to display (-1 for all)
        ),
        $atts,
        'area'
    );
    
    // Get current developer if we're on a developer archive page
    $current_area_id = 0;
    if (is_tax('area')) {
        $current_area = get_queried_object();
        if ($current_area && isset($current_area->term_id)) {
            $current_area_id = $current_area->term_id;
        }
    }
    
    // Set up arguments for getting developers
    $args = array(
        'taxonomy' => 'area',
        'hide_empty' => false, // Changed to false to show all developers
        'number' => $atts['limit'] > 0 ? $atts['limit'] : 0, // Fixed limit parameter
        'orderby' => 'name',
        'order' => 'ASC',
    );
    
    // Exclude current developer if requested
    if ($atts['exclude_current'] === 'yes' && $current_area_id > 0) {
        $args['exclude'] = array($current_area_id);
    }
    
    // Get developer terms
    $area = get_terms($args);
    
    // Debug output to check what we're getting
    // echo '<pre>' . print_r($developers, true) . '</pre>';
    
    // If no developers found or error
    if (empty($area) || is_wp_error($area)) {
        return '<div class="no-nearby-area-message">No developers found. Please check if the "area" taxonomy exists and has terms.</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles
    echo '<style>
        .areas-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        
        .area-card {
            flex: 0 0 calc(25% - 15px);
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .area-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .area-image {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
        }
        
        .area-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .area-card:hover .area-image img {
            transform: scale(1.05);
        }
        
        .area-title {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
            font-family: "Lora", serif;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .area-card {
                flex: 0 0 calc(33.333% - 14px);
            }
        }
        
        @media (max-width: 991px) {
            .area-card {
                flex: 0 0 calc(50% - 10px);
            }
            
            .area-image {
                height: 200px;
            }
        }
        
        @media (max-width: 576px) {
            .areas-grid {
                gap: 15px;
            }
            
            .area-card {
                flex: 0 0 100%;
            }
            
            .area-image {
                height: 180px;
            }
            
            .area-title {
                padding: 12px;
                font-size: 16px;
            }
        }
    </style>';
    
    // HTML output
    echo '<div class="areas-grid">';
    
    // Counter to keep track of how many we've displayed
    $count = 0;
    
    foreach ($areas as $area) {
        // Get featured image for the developer term
        $image_url = '';
        
        // Try to get a term meta image first (ACF, SCF, or other term meta)
        $thumbnail_id = get_term_meta($area->term_id, 'feature_image', true);
        if (!$thumbnail_id) {
            $thumbnail_id = get_term_meta($area->term_id, 'image', true);
        }
        if (!$thumbnail_id) {
            $thumbnail_id = get_term_meta($area->term_id, 'custom_feature_image', true);
        }
        
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
        }
        
        // If no term meta image, get image from a property of this developer
        if (!$image_url) {
            $args = array(
                'post_type' => 'property',
                'posts_per_page' => 1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'area',
                        'field' => 'term_id',
                        'terms' => $area->term_id,
                    ),
                ),
            );
            
            $property_query = new WP_Query($args);
            if ($property_query->have_posts()) {
                while ($property_query->have_posts()) {
                    $property_query->the_post();
                    if (has_post_thumbnail()) {
                        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                    }
                }
                wp_reset_postdata();
            }
        }
        
        // If still no image, use a placeholder
        if (!$image_url) {
            $image_url = 'https://via.placeholder.com/800x600?text=' . urlencode($area->name);
        }
        
        // Generate area URL
        $area_url = get_term_link($area);
        
        // Output developer card
        echo '<a href="' . esc_url($area_url) . '" class="area-card">';
        echo '<div class="area-image">';
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($area->name) . '">';
        echo '</div>';
        echo '<div class="area-title">' . esc_html($area->name) . '</div>';
        echo '</a>';
        
        $count++;
    }
    
    echo '</div>'; // End grid
    
    // For debugging
    // echo '<div>Found ' . $count . ' areas</div>';
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('nearby_area', 'nearby_area_shortcode');

