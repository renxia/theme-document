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

    // 兼容历史返回结构：完整 link_* 字段 + link_category（分类 term_id 数组）
    // 说明：get_bookmarks() 返回的是 wp_links 原始行，本身不含 link_category，
    // 这里参照 get_bookmark() 的做法补上分类 term_id，供外部按分类消费。
    $ids     = array_map( 'intval', wp_list_pluck( $bookmarks, 'link_id' ) );
    $cat_map = array();

    if ( $ids ) {
        // 一次查回全部分类关系，避免逐条链接 N+1 查询
        $terms = wp_get_object_terms( $ids, 'link_category', array( 'fields' => 'all_with_object_id' ) );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $cat_map[ (int) $term->object_id ][] = (int) $term->term_id;
            }
        }
    }

    $result = array();
    foreach ( $bookmarks as $link ) {
        // 保留数据库返回的全部原有字段（link_id / link_url / link_name / link_rating ...），
        // 仅补上 link_category，避免再次改变对外契约
        $item                  = (array) $link;
        $item['link_category'] = isset( $cat_map[ (int) $link->link_id ] ) ? $cat_map[ (int) $link->link_id ] : array();
        $result[]              = $item;
    }

    die( wp_json_encode( $result ) );
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
