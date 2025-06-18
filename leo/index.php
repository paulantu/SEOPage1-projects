<div class="e-con-inner mrmiles-ajax-item <?php echo is_sticky( get_the_ID() ) ? "sticky-ajax-item" : ""; ?>">
            <div class="elementor-element elementor-element-7ff31c91 e-con-full e-flex e-con e-child" data-id="7ff31c91"
                data-element_type="container">
                <div class="elementor-element elementor-element-25a01c4 elementor-widget elementor-widget-theme-post-featured-image elementor-widget-image"
                    data-id="25a01c4" data-element_type="widget" data-widget_type="theme-post-featured-image.default">
                    <div class="elementor-widget-container">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="elementor-element elementor-element-1399c6d5 e-con-full e-flex e-con e-child" data-id="1399c6d5"
                data-element_type="container">
                <div class="elementor-element elementor-element-98f7339 elementor-widget elementor-widget-heading"
                    data-id="98f7339" data-element_type="widget" data-widget_type="heading.default">
                    <div class="elementor-widget-container">
                        <h2 class="elementor-heading-title elementor-size-default">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                    </div>
                </div>
                <div class="elementor-element elementor-element-2c707bbb elementor-widget elementor-widget-post-info"
                    data-id="2c707bbb" data-element_type="widget" data-widget_type="post-info.default">
                    <div class="elementor-widget-container">
                        <ul class="elementor-inline-items elementor-icon-list-items elementor-post-info">
                            <li class="elementor-icon-list-item elementor-repeater-item-052e76b elementor-inline-item"
                                itemprop="datePublished">
                                <span class="elementor-icon-list-icon">
                                    <svg aria-hidden="true" class="e-font-icon-svg e-fas-calendar" viewBox="0 0 448 512"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 192h424c6.6 0 12 5.4 12 12v260c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V204c0-6.6 5.4-12 12-12zm436-44v-36c0-26.5-21.5-48-48-48h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v36c0 6.6 5.4 12 12 12h424c6.6 0 12-5.4 12-12z">
                                        </path>
                                    </svg>
                                </span>
                                <span
                                    class="elementor-icon-list-text elementor-post-info__item elementor-post-info__item--type-date">
                                    <time><?php echo get_the_date('d M Y'); ?></time>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="elementor-element elementor-element-6364d23 elementor-widget elementor-widget-post-info"
                    data-id="6364d23" data-element_type="widget" data-widget_type="post-info.default">
                    <div class="elementor-widget-container">
                        <ul class="elementor-inline-items elementor-icon-list-items elementor-post-info">
                            <li class="elementor-icon-list-item elementor-repeater-item-052e76b elementor-inline-item"
                                itemprop="about">
                                <span class="elementor-icon-list-icon">
                                    <svg aria-hidden="true" class="e-font-icon-svg e-fas-tags" viewBox="0 0 640 512"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M497.941 225.941L286.059 14.059A48 48 0 0 0 252.118 0H48C21.49 0 0 21.49 0 48v204.118a48 48 0 0 0 14.059 33.941l211.882 211.882c18.744 18.745 49.136 18.746 67.882 0l204.118-204.118c18.745-18.745 18.745-49.137 0-67.882zM112 160c-26.51 0-48-21.49-48-48s21.49-48 48-48 48 21.49 48 48-21.49 48-48 48zm513.941 133.823L421.823 497.941c-18.745 18.745-49.137 18.745-67.882 0l-.36-.36L527.64 323.522c16.999-16.999 26.36-39.6 26.36-63.64s-9.362-46.641-26.36-63.64L331.397 0h48.721a48 48 0 0 1 33.941 14.059l211.882 211.882c18.745 18.745 18.745 49.137 0 67.882z">
                                        </path>
                                    </svg>
                                </span>
                                <span
                                    class="elementor-icon-list-text elementor-post-info__item elementor-post-info__item--type-terms">
                                    <span class="elementor-post-info__terms-list">
                                        <?php
            $post_categories = get_the_category();
            if ( $post_categories ) {
              $category_links = array();

              foreach ( $post_categories as $category ) {
                $category_links[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" class="elementor-post-info__terms-list-item">' . esc_html( $category->name ) . '</a>';
              }

              echo implode(', ', $category_links);
            }
          ?>
                                    </span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>