<?php
/**
 * Hide all types of AdSense ads for premium members
 * Add this code to your theme's functions.php file or in a site-specific plugin
 */

// Function to check if current user is a premium member
function is_premium_member() {
    // Check if MemberPress is active
    if (!class_exists('MeprUser')) {
        return false;
    }

    // Get current user
    $mepr_user = new MeprUser(get_current_user_id());

    // Get all active memberships for the user
    $active_memberships = $mepr_user->active_product_subscriptions();

    // Replace these IDs with your premium membership level IDs
    $premium_membership_ids = array(123, 456); // Update these with your actual premium membership IDs

    // Check if user has any premium memberships
    foreach ($active_memberships as $membership_id) {
        if (in_array($membership_id, $premium_membership_ids)) {
            return true;
        }
    }

    return false;
}

// Function to modify AdSense code output
function modify_adsense_display($content) {
    // If user is premium member, remove AdSense code
    if (is_premium_member()) {
        // Remove standard AdSense code blocks
        $content = preg_replace('/<ins class="adsbygoogle"[\s\S]*?<\/ins>/m', '', $content);
        $content = preg_replace('/<script async src="[^"]*adsbygoogle\.js[^"]*"><\/script>/m', '', $content);
        
        // Remove any inline AdSense scripts
        $content = preg_replace('/<script>[^<]*google_ad_client[^<]*<\/script>/m', '', $content);
    }
    
    return $content;
}

// Add filter to modify content
add_filter('the_content', 'modify_adsense_display', 999);
add_filter('widget_text', 'modify_adsense_display', 999);

// Function to handle all types of Google Ads including Auto Ads
function disable_all_google_ads_for_premium() {
    if (is_premium_member()) {
        ?>
        <script>
            // Disable Auto Ads (including vignette ads)
            window.googletag = window.googletag || {};
            window.googletag.cmd = window.googletag.cmd || [];
            window.googletag.cmd.push(function() {
                googletag.pubads().setPrivacySettings({
                    'restrictDataProcessing': true
                });
            });
            
            // Prevent AdSense auto-ads from loading
            window.adsbygoogle = window.adsbygoogle || [];
            function preventAutoAds() {
                if (typeof adsbygoogle.pauseAdRequests === 'function') {
                    adsbygoogle.pauseAdRequests = function() { return true; };
                    adsbygoogle.enableAutoAds = false;
                }
                if (typeof google_ad_modifications !== 'undefined') {
                    google_ad_modifications.preventAutoAds = true;
                }
            }
            
            // Run on page load
            preventAutoAds();
            
            // Also run after a short delay to catch late loading ads
            setTimeout(preventAutoAds, 1000);
            
            // Remove any existing ad containers
            document.addEventListener('DOMContentLoaded', function() {
                const adElements = document.querySelectorAll('.adsbygoogle, .google-auto-placed, [id*="google_ads"], [id*="google_vignette"]');
                adElements.forEach(el => el.remove());
            });
        </script>
        <style type="text/css">
            .adsbygoogle,
            .adsense-container,
            .google-ads,
            .google-auto-placed,
            #google_vignette,
            div[id*="google_ads"],
            iframe[id*="google_ads"] {
                display: none !important;
                position: absolute !important;
                left: -9999px !important;
            }
            
            /* Prevent the vignette overlay */
            body { overflow: auto !important; }
            html { overflow: auto !important; }
        </style>
        <?php
    }
}
add_action('wp_head', 'disable_all_google_ads_for_premium', 1);

// Optional: Add this if ads still show up in the footer
function disable_ads_footer() {
    if (is_premium_member()) {
        ?>
        <script>
            preventAutoAds();
        </script>
        <?php
    }
}
add_action('wp_footer', 'disable_ads_footer', 999);