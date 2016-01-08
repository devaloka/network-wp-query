<?php
/**
 * Network WP Query Template functions.
 *
 * @author Whizark <devaloka@whizark.com>
 * @see http://whizark.com
 * @copyright Copyright (C) 2015 Whizark.
 * @license GPL-2.0
 * @license GPL-3.0
 */

if (!function_exists('get_the_site_ID')) {
    /**
     * Gets the site ID which a post belongs to.
     *
     * @param WP_Post|null $post The post.
     *
     * @return int
     */
    function get_the_site_ID($post = null)
    {
        $post = ($post !== null) ? $post : get_post();

        if (empty($post) || !isset($post->site_ID)) {
            return get_current_blog_id();
        }

        return (int) $post->site_ID;
    }
}

if (!function_exists('the_site_ID')) {
    /**
     * Display the site ID which a post belongs to.
     *
     * @param WP_Post|null $post The post.
     */
    function the_site_ID($post = null)
    {
        echo get_the_site_ID($post);
    }
}
