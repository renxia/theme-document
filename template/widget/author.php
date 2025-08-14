<?php

    /*
 * 作者信息卡片样式
 * @author 友人a丶
 * @date 2022-07-08
 * */

    global $wpdb, $table_prefix;     //数据库对象，表前缀
    $count_posts = wp_count_posts(); //文章数量

    $publish = 0; //已发布

    /*$catelog = wp_count_terms('category') + wp_count_terms('post_tag');*///分类

    /*
 * 获取总浏览量
 * */
    $sql    = 'select sum(`meta_value`+0) As views from `' . $table_prefix . 'postmeta` where `meta_key` = "views"';
    $result = $wpdb->get_results($sql, ARRAY_A);
    $views  = $result[0]['views']; //总阅读数

    $comment = count(get_comments()); //评论总数

    if ($count_posts) {
        /* 已发布的数量 */
        $publish = $count_posts->publish;
    }

    // 取用户数
    $users_count = count_users()['total_users'];

    // 最近更新
    $last       = $wpdb->get_results("SELECT MAX(post_modified) AS MAX_m FROM $wpdb->posts WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'private')");
    $lastUpdate = date('Y-n-j', strtotime($last[0]->MAX_m));

    $linkCount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");
?>

<!--作者信息-->
<div class="author">
    <div class="author-beijin">
        <img loading="lazy" src="<?php echo $beijin; ?>" title="作者头像"/>
    </div>
    <div class="offset">
        <div class="author-avatar">
            <img loading="lazy" src="<?php echo $avatar; ?>" title="作者头像"/>
        </div>
        <div class="author-info">
            <div class="nickname">
                <?php echo $nickname; ?>
            </div>
            <div class="tag">
                <?php echo $profession; ?>
            </div>
        </div>
        <div class="author-self">
            <?php echo $description; ?>
        </div>
        <div class="statistic">
            <div class="item">
                <span class="top">用户数</span>
                <span class="bottom"><?php echo $users_count; ?></span>
            </div>
            <div class="item">
                <span class="top">标签数</span>
                <span class="bottom"><?php echo wp_count_terms('post_tag'); ?></span>
            </div>
            <div class="item">
                <span class="top">链接数</span>
                <span class="bottom" id="siteCreateDate"><?php echo $linkCount; ?></span>
            </div>
            <div class="item">
                <span class="top">文章数</span>
                <span class="bottom"><?php echo $publish; ?></span>
            </div>
            <div class="item">
                <span class="top">评论数</span>
                <span class="bottom"><?php echo $comment; ?></span>
            </div>
            <div class="item">
                <span class="top">阅读数</span>
                <span class="bottom"><?php echo $views; ?></span>
            </div>
            <?php if (! empty($createdate)) {?>
                <div class="item">
                    <span class="top">运行天数</span>
                    <span class="bottom" id="runDays"><?php echo floor((time() - strtotime($createdate)) / 86400); ?></span>
                </div>
                <div class="item">
                    <span class="top">建站时间</span>
                    <span class="bottom" id="siteCreateDate"><?php echo $createdate; ?></span>
                </div>
            <?php }?>
            <div class="item">
                <span class="top">最后更新</span>
                <span class="bottom"><?php echo $lastUpdate; ?></span>
            </div>
        </div>
    </div>
</div>
