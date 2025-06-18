<?php
function show_current_category_with_count() {
    $category = get_queried_object();
    if ($category && !is_wp_error($category)) {
        $output = '<div class="category-header">';
        $output .= '<span class="category-name">' . esc_html($category->name) . '</span>';
        $output .= '<span class="category-count">(' . $category->count . ')</span>';
        $output .= '</div>';
        return $output;
    }
    return '';
}
add_shortcode('current_category_count', 'show_current_category_with_count');


function list_all_categories_with_archive_style() {
    $categories = get_categories(array(
        'hide_empty' => false, // change to true to hide empty categories
    ));

    if (empty($categories)) {
        return '<p>No categories found.</p>';
    }

    $output = '<div class="category-archive-grid">';

    foreach ($categories as $category) {
        $category_link = esc_url(get_category_link($category->term_id));
        $category_name = esc_html($category->name);
        $category_count = intval($category->count);

        $output .= '<div class="category-card">';
        $output .= '<a href="' . $category_link . '">';
        $output .= '<div class="category-card-inner">';
        $output .= '<h3 class="category-title">' . $category_name . '</h3>';
        $output .= '<span class="category-count">(' . $category_count . ')</span>';
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('all_category_archive_style', 'list_all_categories_with_archive_style');


function category_archive_grid_shortcode() {
    if (!is_single()) {
        return ''; // Optional: only show on single post pages
    }

    $categories = get_categories(array(
        'hide_empty' => false,
    ));

    if (empty($categories)) {
        return '<p>No categories found.</p>';
    }

    $output = '<div class="category-archive-grid">';

    foreach ($categories as $category) {
        $category_link = esc_url(get_category_link($category->term_id));
        $category_name = esc_html($category->name);
        $category_count = intval($category->count);

        $output .= '<div class="category-card">';
        $output .= '<a href="' . $category_link . '">';
        $output .= '<div class="category-card-inner">';
        $output .= '<h3 class="category-title">' . $category_name . '</h3>';
        $output .= '<span class="category-count">(' . $category_count . ')</span>';
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('single_post_category_archive', 'category_archive_grid_shortcode');

?>