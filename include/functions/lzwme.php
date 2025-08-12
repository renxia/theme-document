<?php

//判断阅读数量是否需要增加并进行操作
if (!function_exists('the_views_add')) {
    function the_views_add($post_ID, $count, $key, $ajax = false)
    {
        if (is_single() || is_page() || $ajax) {
            if (empty($count)) {
                $count = get_post_meta( $postID, $key, true );
            }

            if ($count == '') {
                $count = 1;
                delete_post_meta( $postID, $count_key );
                add_post_meta( $postID, $count_key, '1' );
            } else {
                update_post_meta($post_ID, $key, $count + 1);
                $count++;
            }
        }

        return $count;
    }
}

//获取当前的阅读数量与自增
if (!function_exists('the_views')) {
    function the_views($post_id = null, $echo = true, $ajax = false)
    {
        global $post;
        if ($post_id == null) {
            $post_id = $post->ID;
        }
        $key = 'views';
        $count = get_post_meta($post_id, $key, true);
        if ($count == '') {
            $count = 0;
        }
        $count = the_views_add($post_id, $count, $key, $ajax);
        $count_view = number_format_i18n($count);
        if (!$echo) {
            return $count_view;
        }
        echo $count_view;
    }
}
