<?php
/**
 * Plugin Name: Property Post Submission
 * Description: Allows logged-in users to submit property posts with SCF fields from the frontend
 * Version: 1.0
 * Author: Antu Paul
 * Author URI: https://www.apaul.dev
 * Text Domain: property-submission
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Property_Post_Submission {
    
    public function __construct() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcode for the form
        add_shortcode('property_submission_form', array($this, 'render_submission_form'));
        
        // Handle form submission
        add_action('wp_ajax_submit_property_post', array($this, 'handle_post_submission'));
        add_action('wp_ajax_nopriv_submit_property_post', array($this, 'handle_not_logged_in'));
        
        // AJAX for taxonomy suggestions
        add_action('wp_ajax_get_property_taxonomy_suggestions', array($this, 'get_taxonomy_suggestions'));
        add_action('wp_ajax_nopriv_get_property_taxonomy_suggestions', array($this, 'handle_not_logged_in'));
        
        // AJAX for media uploads
        add_action('wp_ajax_upload_property_media', array($this, 'handle_media_upload'));
        add_action('wp_ajax_nopriv_upload_property_media', array($this, 'handle_not_logged_in'));
    }
    
    /**
     * Handle media upload through AJAX
     */
    public function handle_media_upload() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'property_submission_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'property-submission')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to upload files.', 'property-submission')));
        }
        
        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file was uploaded.', 'property-submission')));
        }
        
        // Check for upload errors
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('Upload failed. Error code: ', 'property-submission') . $_FILES['file']['error']));
        }
        
        // Require WordPress media handling files
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Handle file upload
        $attachment_id = media_handle_upload('file', 0);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(array('message' => $attachment_id->get_error_message()));
        }
        
        // Get attachment URL
        $attachment_url = wp_get_attachment_url($attachment_id);
        
        // Return success
        wp_send_json_success(array(
            'id' => $attachment_id,
            'url' => $attachment_url
        ));
    }
    
    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_media();
        
        wp_enqueue_style(
            'property-submission-style',
            plugin_dir_url(__FILE__) . 'css/property-submission.css',
            array(),
            '1.0'
        );
        
        wp_enqueue_script(
            'property-submission-script',
            plugin_dir_url(__FILE__) . 'js/property-submission.js',
            array('jquery'),
            '1.0',
            true
        );
        
        wp_localize_script(
            'property-submission-script',
            'property_submission',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('property_submission_nonce'),
                'media_title' => __('Select or Upload Media', 'property-submission'),
                'media_button' => __('Use this media', 'property-submission'),
            )
        );
    }
    
    /**
     * Render the submission form via shortcode
     */
    public function render_submission_form($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to submit a property.', 'property-submission') . '</p>';
        }
        
        // Start output buffering
        ob_start();
        
        // Get all taxonomies for properties
        $amenities = get_terms(array(
            'taxonomy' => 'amenitie',
            'hide_empty' => false,
        ));
        
        $areas = get_terms(array(
            'taxonomy' => 'area',
            'hide_empty' => false,
        ));
        
        $attractions = get_terms(array(
            'taxonomy' => 'attraction',
            'hide_empty' => false,
        ));
        
        $categories = get_terms(array(
            'taxonomy' => 'property-category',
            'hide_empty' => false,
        ));
        
        $locations = get_terms(array(
            'taxonomy' => 'property-location',
            'hide_empty' => false,
        ));
        
        $property_types = get_terms(array(
            'taxonomy' => 'property-type',
            'hide_empty' => false,
        ));
        
        $communities = get_terms(array(
            'taxonomy' => 'residential-community',
            'hide_empty' => false,
        ));
        
        $tags = get_terms(array(
            'taxonomy' => 'custom-tag',
            'hide_empty' => false,
        ));
        ?>
        <div class="property-submission-form-wrapper">
            <div class="response-message"></div>
            
            <form id="property-submission-form" class="property-submission-form">
                <?php wp_nonce_field('property_submission', 'property_submission_nonce'); ?>
                
                <div class="form-group">
                    <label for="post_title"><?php _e('Property Title', 'property-submission'); ?> <span class="required">*</span></label>
                    <input type="text" id="post_title" name="post_title" required>
                </div>
                
                <div class="form-group">
                    <label for="post_excerpt"><?php _e('Property Excerpt', 'property-submission'); ?></label>
                    <textarea id="post_excerpt" name="post_excerpt" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="post_thumbnail"><?php _e('Featured Image', 'property-submission'); ?></label>
                    <div class="file-upload-wrapper">
                        <div class="upload-preview" id="featured-image-preview">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-text">
                                <p>Drag and drop an image here or click to select</p>
                                <button type="button" class="select-media-btn">Select Media</button>
                            </div>
                        </div>
                        <input type="hidden" id="featured_image_id" name="featured_image_id" value="">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="post_content"><?php _e('Description', 'property-submission'); ?> <span class="required">*</span></label>
                    <?php 
                    if (function_exists('wp_editor')) {
                        $settings = array(
                            'media_buttons' => false,
                            'textarea_name' => 'post_content',
                            'textarea_rows' => 10,
                            'teeny' => true,
                        );
                        wp_editor('', 'post_content', $settings);
                    } else {
                        echo '<textarea id="post_content" name="post_content" rows="10" required></textarea>';
                    }
                    ?>
                </div>

                <!-- SCF Fields -->
                <h3><?php _e('Property Details', 'property-submission'); ?></h3>
                
                <!-- Overview Repeater -->
                <div class="form-group scf-repeater" id="overview_repeater">
                    <label><?php _e('Overview', 'property-submission'); ?></label>
                    <div class="repeater-items"></div>
                    <template class="repeater-template">
                        <div class="repeater-item">
                            <div class="form-group">
                                <label><?php _e('Icon', 'property-submission'); ?></label>
                                <div class="file-upload-wrapper">
                                    <div class="upload-preview overview-icon-preview">
                                        <div class="upload-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                        </div>
                                        <div class="upload-text">
                                            <p>Drag and drop an icon here or click to select</p>
                                            <button type="button" class="select-media-btn">Select Media</button>
                                        </div>
                                    </div>
                                    <input type="hidden" class="overview-icon-id" name="overview[__index__][icon]" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php _e('Title', 'property-submission'); ?></label>
                                <input type="text" name="overview[__index__][title]" class="overview-title" value="">
                            </div>
                            <div class="form-group">
                                <label><?php _e('Value', 'property-submission'); ?></label>
                                <input type="text" name="overview[__index__][value]" class="overview-value" value="">
                            </div>
                            <button type="button" class="remove-repeater-item">&times;</button>
                        </div>
                    </template>
                    <button type="button" class="add-repeater-item" data-repeater="overview"><?php _e('Add Overview Item', 'property-submission'); ?></button>
                </div>
                
                <!-- Property Details Repeater -->
                <div class="form-group scf-repeater" id="property_details_repeater">
                    <label><?php _e('Property Details', 'property-submission'); ?></label>
                    <div class="repeater-items"></div>
                    <template class="repeater-template">
                        <div class="repeater-item">
                            <div class="form-group">
                                <label><?php _e('Title', 'property-submission'); ?></label>
                                <input type="text" name="property_details[__index__][title]" class="property-details-title" value="">
                            </div>
                            <div class="form-group">
                                <label><?php _e('Value', 'property-submission'); ?></label>
                                <input type="text" name="property_details[__index__][value]" class="property-details-value" value="">
                            </div>
                            <button type="button" class="remove-repeater-item">&times;</button>
                        </div>
                    </template>
                    <button type="button" class="add-repeater-item" data-repeater="property_details"><?php _e('Add Property Detail', 'property-submission'); ?></button>
                </div>
                
                <!-- Amenities Repeater -->
                <div class="form-group scf-repeater" id="amenities_repeater">
                    <label><?php _e('Amenities', 'property-submission'); ?></label>
                    <div class="repeater-items"></div>
                    <template class="repeater-template">
                        <div class="repeater-item">
                            <div class="form-group">
                                <label><?php _e('Image', 'property-submission'); ?></label>
                                <div class="file-upload-wrapper">
                                    <div class="upload-preview amenities-image-preview">
                                        <div class="upload-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                        </div>
                                        <div class="upload-text">
                                            <p>Drag and drop an image here or click to select</p>
                                            <button type="button" class="select-media-btn">Select Media</button>
                                        </div>
                                    </div>
                                    <input type="hidden" class="amenities-image-id" name="amenities[__index__][image]" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php _e('Title', 'property-submission'); ?></label>
                                <input type="text" name="amenities[__index__][title]" class="amenities-title" value="">
                            </div>
                            <button type="button" class="remove-repeater-item">&times;</button>
                        </div>
                    </template>
                    <button type="button" class="add-repeater-item" data-repeater="amenities"><?php _e('Add Amenity', 'property-submission'); ?></button>
                </div>
                
                <!-- Starting Price -->
                <div class="form-group">
                    <label for="starting_price"><?php _e('Starting Price', 'property-submission'); ?></label>
                    <input type="number" id="starting_price" name="starting_price" min="0" step="0.01">
                </div>
                
                <!-- Floor Plan Group -->
                <div class="form-group scf-group" id="floor_plan_group">
                    <label><?php _e('Floor Plan', 'property-submission'); ?></label>
                    <div class="group-item">
                        <div class="form-group">
                            <label><?php _e('Description about floor', 'property-submission'); ?></label>
                            <textarea name="floor_plan[description_about_floor]" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php _e('Bedroom', 'property-submission'); ?></label>
                            <input type="number" name="floor_plan[bedroom]" min="0">
                        </div>
                        <div class="form-group">
                            <label><?php _e('Bathroom', 'property-submission'); ?></label>
                            <input type="number" name="floor_plan[bathroom]" min="0">
                        </div>
                        <div class="form-group">
                            <label><?php _e('Living Area', 'property-submission'); ?></label>
                            <input type="number" name="floor_plan[living_area]" min="0">
                        </div>
                        <div class="form-group">
                            <label><?php _e('Floor Plan Image', 'property-submission'); ?></label>
                            <div class="file-upload-wrapper">
                                <div class="upload-preview" id="floor-plan-image-preview">
                                    <div class="upload-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                    </div>
                                    <div class="upload-text">
                                        <p>Drag and drop an image here or click to select</p>
                                        <button type="button" class="select-media-btn">Select Media</button>
                                    </div>
                                </div>
                                <input type="hidden" id="floor_plan_image" name="floor_plan[floor_plan_image]" value="">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="form-group">
                    <label for="location"><?php _e('Location', 'property-submission'); ?></label>
                    <input type="text" id="location" name="location">
                </div>
                
                <!-- Price -->
                <div class="form-group">
                    <label for="price"><?php _e('Price', 'property-submission'); ?></label>
                    <input type="number" id="price" name="price" min="0" step="0.01">
                </div>
                
                <!-- Image Gallery -->
                <div class="form-group">
                    <label for="image_gallery"><?php _e('Image Gallery', 'property-submission'); ?></label>
                    <div class="file-upload-wrapper gallery-upload-wrapper">
                        <div class="upload-preview" id="gallery-preview">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-text">
                                <p>Drag and drop images here or click to select</p>
                                <button type="button" class="select-gallery-btn">Select Images</button>
                            </div>
                        </div>
                        <div class="gallery-images-container"></div>
                        <input type="hidden" id="image_gallery" name="image_gallery" value="">
                    </div>
                </div>
                
                <!-- Video -->
                <div class="form-group">
                    <label for="video"><?php _e('Video', 'property-submission'); ?></label>
                    <div class="file-upload-wrapper">
                        <div class="upload-preview" id="video-preview">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-text">
                                <p>Drag and drop a video here or click to select</p>
                                <button type="button" class="select-media-btn">Select Media</button>
                            </div>
                        </div>
                        <input type="hidden" id="video" name="video" value="">
                    </div>
                </div>
                
                <!-- Property Brochure -->
                <div class="form-group">
                    <label for="property_brochure"><?php _e('Property Brochure', 'property-submission'); ?></label>
                    <div class="file-upload-wrapper">
                        <div class="upload-preview" id="brochure-preview">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-text">
                                <p>Drag and drop a PDF here or click to select</p>
                                <button type="button" class="select-media-btn">Select Media</button>
                            </div>
                        </div>
                        <input type="hidden" id="property_brochure" name="property_brochure" value="">
                    </div>
                </div>
                
                <!-- Taxonomies -->
                <h3><?php _e('Property Classifications', 'property-submission'); ?></h3>
                
                <!-- Property Categories -->
                <div class="form-group">
                    <label for="property_category"><?php _e('Property Categories', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="category_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new categories', 'property-submission'); ?>">
                        <div id="category_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_categories" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_categories" name="property_categories" value="">
                    </div>
                </div>
                
                <!-- Property Types -->
                <div class="form-group">
                    <label for="property_type"><?php _e('Property Types', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="type_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new property types', 'property-submission'); ?>">
                        <div id="type_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_types" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_types" name="property_types" value="">
                    </div>
                </div>
                
                <!-- Property Location -->
                <div class="form-group">
                    <label for="property_location"><?php _e('Property Locations', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="location_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new locations', 'property-submission'); ?>">
                        <div id="location_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_locations" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_locations" name="property_locations" value="">
                    </div>
                </div>
                
                <!-- Amenities Taxonomy -->
                <div class="form-group">
                    <label for="property_amenities"><?php _e('Property Amenities', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="amenities_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new amenities', 'property-submission'); ?>">
                        <div id="amenities_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_amenities" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_amenities" name="property_amenities" value="">
                    </div>
                </div>
                
                <!-- Areas -->
                <div class="form-group">
                    <label for="property_area"><?php _e('Areas', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="area_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new areas', 'property-submission'); ?>">
                        <div id="area_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_areas" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_areas" name="property_areas" value="">
                    </div>
                </div>
                
                <!-- Attractions -->
                <div class="form-group">
                    <label for="property_attraction"><?php _e('Attractions', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="attraction_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new attractions', 'property-submission'); ?>">
                        <div id="attraction_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_attractions" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_attractions" name="property_attractions" value="">
                    </div>
                </div>
                
                <!-- Residential Communities -->
                <div class="form-group">
                    <label for="property_community"><?php _e('Residential Communities', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="community_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new communities', 'property-submission'); ?>">
                        <div id="community_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_communities" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_communities" name="property_communities" value="">
                    </div>
                </div>
                
                <!-- Custom Tags -->
                <div class="form-group">
                    <label for="property_tags"><?php _e('Tags', 'property-submission'); ?></label>
                    <div class="taxonomy-input-wrapper">
                        <input type="text" id="tag_input" class="taxonomy-input" placeholder="<?php _e('Type to search or add new tags', 'property-submission'); ?>">
                        <div id="tag_suggestions" class="taxonomy-suggestions"></div>
                        <div id="selected_tags" class="selected-taxonomies"></div>
                        <input type="hidden" id="property_tags" name="property_tags" value="">
                    </div>
                </div>
                
                <div class="form-group submit-group">
                    <button type="submit" id="submit-property" class="submit-button"><?php _e('Submit Property', 'property-submission'); ?></button>
                </div>
            </form>
        </div>
        <?php
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Handle post submission through AJAX
     */
    public function handle_post_submission() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'property_submission_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'property-submission')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to submit properties.', 'property-submission')));
        }
        
        // Validate required fields
        if (empty($_POST['title']) || empty($_POST['content'])) {
            wp_send_json_error(array('message' => __('Title and description are required.', 'property-submission')));
        }
        
        // Prepare post data
        $post_data = array(
            'post_title'    => sanitize_text_field($_POST['title']),
            'post_content'  => wp_kses_post($_POST['content']),
            'post_excerpt'  => sanitize_textarea_field($_POST['excerpt']),
            'post_status'   => 'publish', // You can change this to 'draft' or 'pending' for moderation
            'post_author'   => get_current_user_id(),
            'post_type'     => 'property', // Set the post type to property
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
        }
        
        // Set featured image if provided
        if (!empty($_POST['featured_image_id'])) {
            $featured_image_id = absint($_POST['featured_image_id']);
            set_post_thumbnail($post_id, $featured_image_id);
        }
        
        // Save SCF fields
        
        // Overview
        if (!empty($_POST['overview']) && is_array($_POST['overview'])) {
            update_post_meta($post_id, 'overview', $_POST['overview']);
        }
        
        // Property Details
        if (!empty($_POST['property_details']) && is_array($_POST['property_details'])) {
            update_post_meta($post_id, 'property_details', $_POST['property_details']);
        }
        
        // Amenities
        if (!empty($_POST['amenities']) && is_array($_POST['amenities'])) {
            update_post_meta($post_id, 'amenities', $_POST['amenities']);
        }
        
        // Starting Price
        if (!empty($_POST['starting_price'])) {
            update_post_meta($post_id, 'starting_price', floatval($_POST['starting_price']));
        }
        
        // Floor Plan
        if (!empty($_POST['floor_plan']) && is_array($_POST['floor_plan'])) {
            update_post_meta($post_id, 'floor_plan', $_POST['floor_plan']);
        }
        
        // Location
        if (!empty($_POST['location'])) {
            update_post_meta($post_id, 'location', sanitize_text_field($_POST['location']));
        }
        
        // Price
        if (!empty($_POST['price'])) {
            update_post_meta($post_id, 'price', floatval($_POST['price']));
        }
        
        // Image Gallery
        if (!empty($_POST['image_gallery'])) {
            $gallery_ids = explode(',', $_POST['image_gallery']);
            update_post_meta($post_id, 'image_gallery', $gallery_ids);
        }
        
        // Video
        if (!empty($_POST['video'])) {
            update_post_meta($post_id, 'video', absint($_POST['video']));
        }
        
        // Property Brochure
        if (!empty($_POST['property_brochure'])) {
            update_post_meta($post_id, 'property_brochure', absint($_POST['property_brochure']));
        }
        
        // Process taxonomies
        
        // Categories
        if (!empty($_POST['property_categories'])) {
            $categories = json_decode(stripslashes($_POST['property_categories']), true);
            $category_ids = array();
            
            foreach ($categories as $category) {
                if (isset($category['id']) && $category['id'] > 0) {
                    // Existing category
                    $category_ids[] = intval($category['id']);
                } else if (isset($category['name']) && !empty($category['name'])) {
                    // New category
                    $term = term_exists($category['name'], 'property-category');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($category['name']), 'property-category');
                        if (!is_wp_error($term)) {
                            $category_ids[] = $term['term_id'];
                        }
                    } else {
                        $category_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($category_ids)) {
                wp_set_object_terms($post_id, $category_ids, 'property-category');
            }
        }
        
        // Get the permalink of the new post
        $post_url = get_permalink($post_id);
        
        wp_send_json_success(array(
            'message' => __('Property published successfully!', 'property-submission'),
            'post_id' => $post_id,
            'post_url' => $post_url
        ));
    }
    
    /**
     * AJAX handler for taxonomy suggestions
     */
    public function get_taxonomy_suggestions() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'property_submission_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'property-submission')));
        }
        
        // Get search term and taxonomy type
        $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_key($_POST['taxonomy']) : '';
        
        $taxonomy_map = array(
            'category' => 'property-category',
            'type' => 'property-type',
            'location' => 'property-location',
            'amenities' => 'amenitie',
            'area' => 'area',
            'attraction' => 'attraction',
            'community' => 'residential-community',
            'tag' => 'custom-tag'
        );
        
        if (empty($taxonomy) || !isset($taxonomy_map[$taxonomy])) {
            wp_send_json_error(array('message' => __('Invalid taxonomy.', 'property-submission')));
        }
        
        $actual_taxonomy = $taxonomy_map[$taxonomy];
        
        $args = array(
            'taxonomy'   => $actual_taxonomy,
            'hide_empty' => false,
            'number'     => 10,
            'name__like' => $search_term
        );
        
        $terms = get_terms($args);
        $suggestions = array();
        
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $suggestions[] = array(
                    'id'   => $term->term_id,
                    'name' => $term->name
                );
            }
        }
        
        wp_send_json_success(array('suggestions' => $suggestions));
    }
    
    /**
     * Handle unauthorized access
     */
    public function handle_not_logged_in() {
        wp_send_json_error(array('message' => __('You must be logged in to perform this action.', 'property-submission')));
    }
}

// Initialize the plugin
$property_post_submission = new Property_Post_Submission();
        
        // Property Types
        if (!empty($_POST['property_types'])) {
            $types = json_decode(stripslashes($_POST['property_types']), true);
            $type_ids = array();
            
            foreach ($types as $type) {
                if (isset($type['id']) && $type['id'] > 0) {
                    $type_ids[] = intval($type['id']);
                } else if (isset($type['name']) && !empty($type['name'])) {
                    $term = term_exists($type['name'], 'property-type');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($type['name']), 'property-type');
                        if (!is_wp_error($term)) {
                            $type_ids[] = $term['term_id'];
                        }
                    } else {
                        $type_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($type_ids)) {
                wp_set_object_terms($post_id, $type_ids, 'property-type');
            }
        }
        
        // Property Locations
        if (!empty($_POST['property_locations'])) {
            $locations = json_decode(stripslashes($_POST['property_locations']), true);
            $location_ids = array();
            
            foreach ($locations as $location) {
                if (isset($location['id']) && $location['id'] > 0) {
                    $location_ids[] = intval($location['id']);
                } else if (isset($location['name']) && !empty($location['name'])) {
                    $term = term_exists($location['name'], 'property-location');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($location['name']), 'property-location');
                        if (!is_wp_error($term)) {
                            $location_ids[] = $term['term_id'];
                        }
                    } else {
                        $location_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($location_ids)) {
                wp_set_object_terms($post_id, $location_ids, 'property-location');
            }
        }
        
        // Amenities Taxonomy
        if (!empty($_POST['property_amenities'])) {
            $amenities = json_decode(stripslashes($_POST['property_amenities']), true);
            $amenity_ids = array();
            
            foreach ($amenities as $amenity) {
                if (isset($amenity['id']) && $amenity['id'] > 0) {
                    $amenity_ids[] = intval($amenity['id']);
                } else if (isset($amenity['name']) && !empty($amenity['name'])) {
                    $term = term_exists($amenity['name'], 'amenitie');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($amenity['name']), 'amenitie');
                        if (!is_wp_error($term)) {
                            $amenity_ids[] = $term['term_id'];
                        }
                    } else {
                        $amenity_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($amenity_ids)) {
                wp_set_object_terms($post_id, $amenity_ids, 'amenitie');
            }
        }
        
        // Areas
        if (!empty($_POST['property_areas'])) {
            $areas = json_decode(stripslashes($_POST['property_areas']), true);
            $area_ids = array();
            
            foreach ($areas as $area) {
                if (isset($area['id']) && $area['id'] > 0) {
                    $area_ids[] = intval($area['id']);
                } else if (isset($area['name']) && !empty($area['name'])) {
                    $term = term_exists($area['name'], 'area');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($area['name']), 'area');
                        if (!is_wp_error($term)) {
                            $area_ids[] = $term['term_id'];
                        }
                    } else {
                        $area_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($area_ids)) {
                wp_set_object_terms($post_id, $area_ids, 'area');
            }
        }
        
        // Attractions
        if (!empty($_POST['property_attractions'])) {
            $attractions = json_decode(stripslashes($_POST['property_attractions']), true);
            $attraction_ids = array();
            
            foreach ($attractions as $attraction) {
                if (isset($attraction['id']) && $attraction['id'] > 0) {
                    $attraction_ids[] = intval($attraction['id']);
                } else if (isset($attraction['name']) && !empty($attraction['name'])) {
                    $term = term_exists($attraction['name'], 'attraction');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($attraction['name']), 'attraction');
                        if (!is_wp_error($term)) {
                            $attraction_ids[] = $term['term_id'];
                        }
                    } else {
                        $attraction_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($attraction_ids)) {
                wp_set_object_terms($post_id, $attraction_ids, 'attraction');
            }
        }
        
        // Communities
        if (!empty($_POST['property_communities'])) {
            $communities = json_decode(stripslashes($_POST['property_communities']), true);
            $community_ids = array();
            
            foreach ($communities as $community) {
                if (isset($community['id']) && $community['id'] > 0) {
                    $community_ids[] = intval($community['id']);
                } else if (isset($community['name']) && !empty($community['name'])) {
                    $term = term_exists($community['name'], 'residential-community');
                    if (!$term) {
                        $term = wp_insert_term(sanitize_text_field($community['name']), 'residential-community');
                        if (!is_wp_error($term)) {
                            $community_ids[] = $term['term_id'];
                        }
                    } else {
                        $community_ids[] = $term['term_id'];
                    }
                }
            }
            
            if (!empty($community_ids)) {
                wp_set_object_terms($post_id, $community_ids, 'residential-community');
            }
        }
        
        // Tags
        if (!empty($_POST['property_tags'])) {
            $tags = json_decode(stripslashes($_POST['property_tags']), true);
            $tag_names = array();
            
            foreach ($tags as $tag) {
                if (isset($tag['name']) && !empty($tag['name'])) {
                    $tag_names[] = sanitize_text_field($tag['name']);
                }
            }
            
            if (!empty($tag_names)) {
                wp_set_object_terms($post_id, $tag_names, 'custom-tag');
            }
        }