<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * 侧边栏最新评论
 * @author 友人a丶
 * @date 2022-07-08
 * */
?>

<div class="div-info">
    <div class="header">
        <ul>
            <li class="active"><div class="mark"></div><?php echo esc_html($title); ?></li>
        </ul>
    </div>
    <ul class="ul" style="margin-top: 0.5rem;">

        <?php
        /*
         * 判断显示类型
         * */
        $comments = get_comments([
            "number" => absint($number),
            'status' => 'approve',
            "orderby" => "comment_date_gmt",
            "include_unapproved" => false
        ]);

        /*
         * 递归显示文章
         * */
        foreach ($comments as $comment) {

            $avatar     = get_avatar_url($comment->user_id);
            $author      = $comment->comment_author;
            $content     = wp_strip_all_tags($comment->comment_content);
            $comment_url = get_the_permalink($comment->comment_post_ID);
            ?>
            <li>
                <div class="comment-widget">
                    <img loading="lazy" class="author-avatar" src="<?php echo esc_url($avatar); ?>" title="头像"/>
                    <a href="<?php echo esc_url($comment_url); ?>" title="<?php echo esc_attr($content); ?>">
                        <span class="name"><?php echo esc_html($author); ?></span>
                        <span class="comment-widget-content"><?php echo esc_html($content); ?></span>
                    </a>
                </div>
            </li>
            <?php
        }
        wp_reset_query(); //重置文章指指针
        ?>
    </ul>

</div>
