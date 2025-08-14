<?php
/*
Template Name: 友情链接(list)
*/

// 支持 json api 按分类获取友情链接
if (isset($_REQUEST['json'])) {
    // https://developer.wordpress.org/reference/functions/get_bookmarks/
    $bookmarks = get_bookmarks(array(
        'orderby' => isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'rating',
        'order' => isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC',
        'limit' => isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) ?? -1 : -1,
        'category' => isset($_REQUEST['cate']) ? $_REQUEST['cate'] : '',
        'hide_invisible' => 1,
        'show_updated' => 1,
        'include' => isset($_REQUEST['include']) ? $_REQUEST['include'] : '',
        'exclude' => isset($_REQUEST['exclude']) ? $_REQUEST['exclude'] : '',
        'search' => isset($_REQUEST['search']) ? $_REQUEST['search'] : '',
    ));

    header('Content-Type:application/json; charset=utf-8');
    isset($_GET['cors']) && header("Access-Control-Allow-Origin:*");
    isset($_REQUEST['nocache']) && header('cache-control: no-cache');

    die(json_encode($bookmarks));
}

get_header();
?>

<main class="main-container">
    <div class="main-main">
        <article class="main-content">
            <!-- 面包屑导航 -->
            <?php get_template_part( './template/index/breadcrumb' ); ?>
            <!-- 文章顶部 -->
            <?php get_template_part( './template/index/article-header' ); ?>
            <!--  文章内容  -->
            <div class="main-article">
                <?php the_content() ?>
            </div>
            <div class="main-link">
                <div class="link-search" style="margin-bottom: 20px">
                    <i class="iconfont icon-sousuo fa fa-search"></i>
                    <input id="linkSearch" type="text" class="form-control" placeholder="[Ctrl+K] Search for...">
                </div>

                <div class="link-list">
                    <?php wp_list_bookmarks('orderby=link_rating&order=DESC&category_orderby=slug&show_images=1');?>
                </div>
            </div>
        </article>
        <!-- 文章评论 -->
        <?php comments_template(); ?>
    </div>
    <?php get_template_part( './template/index/sidebar-right' ); ?>
</main>

<!--角标-->
<?php get_template_part( './template/index/fixed' ); ?>
<?php get_footer(); ?>
