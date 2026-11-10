<?php

    /*
 * 作者信息卡片样式
 * @author 友人a丶
 * @date 2022-07-08
 * */

    global $wpdb;                 // 数据库对象
    $count_posts = wp_count_posts(); //文章数量

    $publish = 0; //已发布

    /*
 * 获取总浏览量：meta_key 通过 prepare 参数化，避免配置值拼接进 SQL
 * */
    $views_field = nicen_theme_config('document_post_views_field', false) ?: 'post_views_count';
    $sql    = $wpdb->prepare(
        "SELECT SUM(`meta_value`+0) AS views FROM {$wpdb->postmeta} WHERE `meta_key` = %s",
        $views_field
    );
    $result = $wpdb->get_results($sql, ARRAY_A);
    $views  = isset($result[0]['views']) ? (int) $result[0]['views'] : 0; //总阅读数

    $comment = count(get_comments()); //评论总数

    if ($count_posts) {
        /* 已发布的数量 */
        $publish = $count_posts->publish;
    }

    // 取用户数
    $users_count = count_users()['total_users'];

    // 最近更新
    $last       = $wpdb->get_results("SELECT MAX(post_modified) AS MAX_m FROM $wpdb->posts WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'private')");
    $lastUpdate = isset($last[0]->MAX_m) ? date('Y-n-j', strtotime($last[0]->MAX_m)) : '';

    $linkCount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");
?>

<!--作者信息-->
<div class="author">
    <div class="author-beijin">
        <img loading="lazy" src="<?php echo esc_url($beijin); ?>" title="作者头像"/>
    </div>
    <div class="offset">
        <div class="author-avatar">
            <img loading="lazy" src="<?php echo esc_url($avatar); ?>" title="作者头像"/>
        </div>
        <div class="author-info">
            <div class="nickname">
                <?php echo esc_html($nickname); ?>
            </div>
            <div class="tag">
                <?php echo esc_html($profession); ?>
            </div>
        </div>
        <div class="author-self">
            <?php echo esc_html($description); ?>
        </div>
        <div class="statistic">
            <div class="item">
                <span class="top">用户数</span>
                <span class="bottom"><?php echo esc_html($users_count); ?></span>
            </div>
            <div class="item">
                <span class="top">标签数</span>
                <span class="bottom"><?php echo esc_html(wp_count_terms('post_tag')); ?></span>
            </div>
            <div class="item">
                <span class="top">链接数</span>
                <span class="bottom" id="siteCreateDate"><?php echo esc_html($linkCount); ?></span>
            </div>
            <div class="item">
                <span class="top">文章数</span>
                <span class="bottom"><?php echo esc_html($publish); ?></span>
            </div>
            <div class="item">
                <span class="top">评论数</span>
                <span class="bottom"><?php echo esc_html($comment); ?></span>
            </div>
            <div class="item">
                <span class="top">阅读数</span>
                <span class="bottom"><?php echo esc_html($views); ?></span>
            </div>
            <?php if (! empty($createdate)) {?>
                <div class="item">
                    <span class="top">运行天数</span>
                    <span class="bottom" id="runDays"><?php echo esc_html(floor((time() - strtotime($createdate)) / 86400)); ?></span>
                </div>
                <div class="item">
                    <span class="top">建站时间</span>
                    <span class="bottom" id="siteCreateDate"><?php echo esc_html($createdate); ?></span>
                </div>
            <?php }?>
            <div class="item">
                <span class="top">最后更新</span>
                <span class="bottom"><?php echo esc_html($lastUpdate); ?></span>
            </div>
        </div>
    </div>
</div>
