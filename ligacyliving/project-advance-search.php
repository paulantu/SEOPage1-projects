<?php
function project_filter_scripts() {
    // Only enqueue if shortcode is used on the page
    global $post;
	$is_archive = is_post_type_archive('project') || is_tax('location') || is_tax('area') || is_tax('projects-type');
    // if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'property_filter')) {
    
        wp_enqueue_style('property-filter-style', get_stylesheet_directory_uri() . '/style.css', array(), filemtime(get_stylesheet_directory() . '/style.css'));


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
                            nonce: "' . wp_create_nonce('project_filter_nonce') . '"
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
                filterProjects();
            });
            
            // Handle map search input keypress
            $(\'#map-search\').on(\'keypress\', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    filterProjects();
                }
            });
            
            // Function to filter properties
            function filterProjects() {
                var locationId = $(\'#location-dropdown\').val();
                var subLocality = $(\'#sub-locality-dropdown\').val();
                var projectTypeId = $(\'#property-type-dropdown\').val();
                var searchTerm = $(\'#map-search\').val();
                
                // Show loading indicator
                $(\'#property-filter-results\').html(\'<div class="loading-properties">Loading properties...</div>\');
                
                // AJAX call to filter properties
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "filter_projects",
                        location_id: locationId,
                        sub_locality: subLocality,
                        project_type_id: projectTypeId,
                        search_term: searchTerm,
                        nonce: "' . wp_create_nonce('project_filter_nonce') . '"
                    },
                    success: function(response) {
                        $(\'#property-filter-results\').html(response.html);
                        
                        // Scroll to results
                        $(\'html, body\').animate({
                            scrollTop: $(\'#property-filter-results\').offset().top - 100
                        }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error filtering projects:", error);
                        $(\'#property-filter-results\').html(\'<div class="error-message">Error loading projects. Please try again.</div>\');
                    }
                });
            }
        });
        ';
        
        wp_register_script('project-filter-inline-script', false);
        wp_enqueue_script('project-filter-inline-script');
        wp_add_inline_script('project-filter-inline-script', $js);
    // }
}
add_action('wp_footer', 'project_filter_scripts');

// Create the property filter shortcode
function project_filter_shortcode() {
    ob_start();
    ?>
    <div class="property-filter-container">
        <div class="property-filter-row">
            <div class="property-filter-item">
                <select id="location-dropdown" class="property-filter-dropdown">
                    <option value="">Select Location</option>
                    <?php
                    // Get all terms from property-location taxonomy
                    $locations = get_terms(array(
                        'taxonomy' => 'location',
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
                    <?php
                    // Get all terms from property-location taxonomy
                    $areas = get_terms(array(
                        'taxonomy' => 'area',
                        'hide_empty' => false,
                    ));
                    
                    if (!empty($areas) && !is_wp_error($areas)) {
                        foreach ($areas as $area) {
                            echo '<option value="' . esc_attr($area->term_id) . '">' . esc_html($area->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="property-filter-item">
                <select id="property-type-dropdown" class="property-filter-dropdown">
                    <option value="">Project Type</option>
                    <?php
                    // Get all terms from property-tag taxonomy
                    $property_types = get_terms(array(
                        'taxonomy' => 'property-type',
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
                <button id="find-properties-btn">Find Projects</button>
            </div>
        </div>
        
        <div id="property-filter-results"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('project_filter', 'project_filter_shortcode');



// AJAX handler for sub-locality dropdown
function get_project_sub_localities_ajax() {
    // Check nonce for security
    check_ajax_referer('project_filter_nonce', 'nonce');
    
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    $sub_localities = array();
    
    if ($location_id) {
        // Get properties with the selected location
        $args = array(
            'post_type' => 'project',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'location',
                    'field' => 'term_id',
                    'terms' => $location_id,
                ),
            ),
        );
        
        $properties = new WP_Query($args);
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                // $sub_locality = get_post_meta(get_the_ID(), 'location', true);
                $sub_locality = wp_get_post_terms(get_the_ID(), 'area', array( 'fields' => 'all' ));
                
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
        foreach ($sub_locality as $area) {
            $response .= '<option value="' . esc_attr($area->term_id) . '">' . esc_html($area->name) . '</option>';
        }
    }
    
    echo $response;
    wp_die();
}
add_action('wp_ajax_get_sub_localities', 'get_project_sub_localities_ajax');
add_action('wp_ajax_nopriv_get_sub_localities', 'get_project_sub_localities_ajax');

// AJAX handler for property filter
function filter_projects_ajax() {
    // Check nonce for security
    check_ajax_referer('project_filter_nonce', 'nonce');
    
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    $sub_locality = isset($_POST['sub_locality']) ? sanitize_text_field($_POST['sub_locality']) : '';
    $project_type_id = isset($_POST['project_type_id']) ? intval($_POST['project_type_id']) : 0;
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    
    // Build query args
    $args = array(
        'post_type' => 'project',
        'posts_per_page' => 12,
        'post_status' => 'publish',
    );
    
    // Tax query for location and Project type
    $tax_query = array();
    
    if ($location_id) {
        $tax_query[] = array(
            'taxonomy' => 'location',
            'field' => 'term_id',
            'terms' => $location_id,
        );
    }
    
    if ($project_type_id) {
        $tax_query[] = array(
            'taxonomy' => 'projects-type',
            'field' => 'term_id',
            'terms' => $project_type_id,
        );
    }

    if ($sub_locality) {
        $tax_query[] = array(
            'taxonomy' => 'area',
            'field' => 'term_id',
            'terms' => $sub_locality,
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    // Meta query for sub-locality
    $meta_query = array();
    
    if (!empty($sub_locality)) {
        // $meta_query[] = array(
        //     'key' => 'location',
        //     'value' => $sub_locality,
        //     'compare' => '=',
        // );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    // Search
    if (!empty($search_term)) {
        $args['s'] = $search_term;
    }
    
    // Run query
    $projects = new WP_Query($args);
    
    $response = array(
        'count' => $projects->found_posts,
        'html' => '',
    );
    
    // Build HTML for results
    if ($projects->have_posts()) {
        ob_start();
        echo '<div class="property-grid">';
        while ($projects->have_posts()) {
            $projects->the_post();
            $project_id = get_the_ID();
            $beds = get_post_meta($project_id, 'bed', true);
            $baths = get_post_meta($project_id, 'bath', true);
            $size = get_post_meta($project_id, 'size', true);
            $starting_price = get_post_meta($project_id, 'starting_price', true);
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
                        <div class="property-price">Starting from AED <?php echo esc_html($starting_price); ?></div>
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
        $response['html'] = '<div class="no-properties-found">No projects found matching your criteria.</div>';
    }
    
    wp_send_json($response);
}
add_action('wp_ajax_filter_projects', 'filter_projects_ajax');
add_action('wp_ajax_nopriv_filter_projects', 'filter_projects_ajax');