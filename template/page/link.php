<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
Template Name: 友情链接(list)
*/

// 支持 json api 按分类获取友情链接
if (isset($_REQUEST['json'])) {

    // 所有入参统一清洗，避免未过滤值进入 get_bookmarks() 的查询拼装
    $req = wp_unslash($_REQUEST);

    // 仅接受标量，避免数组入参导致 sanitize_key/intval 在 PHP 8 抛 TypeError
    $scalar = function ($key) use ($req) {
        return (isset($req[$key]) && is_scalar($req[$key])) ? $req[$key] : null;
    };

    // orderby / order 使用白名单，杜绝任意列名 / 注入
    $orderby_allow = ['id', 'url', 'name', 'target', 'description', 'owner',
                       'rating', 'updated', 'rel', 'notes', 'rss', 'length', 'rand'];
    $orderby = $scalar('orderby') !== null ? strtolower(sanitize_key($scalar('orderby'))) : 'rating';
    if (!in_array($orderby, $orderby_allow, true)) {
        $orderby = 'rating';
    }
    $order = $scalar('order') !== null ? strtoupper(sanitize_key($scalar('order'))) : 'DESC';
    if (!in_array($order, ['ASC', 'DESC'], true)) {
        $order = 'DESC';
    }
    $limit = $scalar('limit') !== null ? intval($scalar('limit')) : -1;

    // 仅保留安全的标量字符串，数组一律忽略
    $text = function ($key) use ($req) {
        return (isset($req[$key]) && is_scalar($req[$key]))
            ? sanitize_text_field($req[$key]) : '';
    };

    $bookmarks = get_bookmarks(array(
        'orderby'        => $orderby,
        'order'          => $order,
        'limit'          => $limit,
        'category'       => $text('cate'),
        'hide_invisible' => 1,
        'show_updated'  => 1,
        'include'        => $text('include'),
        'exclude'        => $text('exclude'),
        'search'         => $text('search'),
    ));

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        isset($_GET['cors']) && header('Access-Control-Allow-Origin: *');
        isset($_REQUEST['nocache']) && header('Cache-Control: no-cache');
    }

    // 只输出展示所需字段，避免泄露 link_owner / link_rss / link_notes 等内部字段
    $result = array_map(function ($link) {
        return [
            'name'        => $link->link_name,
            'image'       => $link->link_image,
            'description' => $link->link_description,
            'url'         => $link->link_url,
            'target'      => $link->link_target,
            'updated'     => $link->link_updated,
        ];
    }, $bookmarks);

    die(wp_json_encode($result));
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
