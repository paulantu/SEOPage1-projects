<?php

/**
 * Floor Plan Display Shortcode with SVG Icons
 */
function display_project_infrastructure_shortcode($atts) {
    // Define shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'project_infrastructure'
    );
    
    $product_id = $atts['product_id'];
    
    // Get floor plan data directly with WordPress functions
    $infrastructure_description = get_post_meta($product_id, 'project_infrastructure_infrastructure_description', true);
    $infrastructure_image = get_post_meta($product_id, 'project_infrastructure_infrastructure_image', true);
    
    // If no essential data found, return empty
    if(empty($infrastructure_description) && empty($infrastructure_image)) {
        return '<div>No infrastructure info available</div>';
    }
    
    // Start output buffer
    ob_start();
    
    // CSS styles for the floor plan display
    echo '<style>
        .infrastructure-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
        }
        .infrastructure-description {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .infrastructure-image-container {
            max-width: 100%;
            text-align: center;
        }
        .infrastructure-image {
            max-width: 100%;
            height: auto;
        }
        .infrastructure-fratures-and-image {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }';
    
    // HTML output
    echo '<div class="infrastructure-container">';
    
    // Description
    if(!empty($infrastructure_description)) {
        echo '<div class="infrastructure-description">' . esc_html($infrastructure_description) . '</div>';
    }
    


echo '<div class="infrastructure-fratures-and-image">';
    
    // Floor Plan Image
    if(!empty($infrastructure_image)) {
        echo '<div class="infrastructure-image-container">';
        $image_url = wp_get_attachment_image_url($infrastructure_image, 'full');
        if($image_url) {
            echo '<img src="' . esc_url($image_url) . '" alt="infrastructure image" class="infrastructure-image">';
        }
        echo '</div>';
    }
    echo '</div>'; 
    echo '</div>'; // End container
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('project_infrastructure', 'display_project_infrastructure_shortcode');
