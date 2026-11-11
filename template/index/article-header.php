<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * 文章顶部模板
 * @author 友人a丶
 * @date 2022-07-08
 * */

?>

    <!--  标题  -->
    <h1>
		<?php echo esc_html( get_the_title() ); ?>
    </h1>

<?php
/*
 * 是否显示文章详情
 * */
if ( nicen_theme_showInfo() ) {

	/*获取作者信息*/
	$author = get_the_author_meta( 'display_name', $post->post_author );
	$url    = get_the_author_meta( 'user_url', $post->post_author );

	$category = nicen_theme_getCategory( get_the_ID() ); //获取文章分类
	$cats     = get_the_category();
	// 无分类时避免访问 [0]->term_id（PHP 8 会告警）
	$link     = ! empty( $cats ) ? get_category_link( $cats[0]->term_id ) : '';


	?>
    <!--  文章信息  -->
    <!--  仅在文章页面展示 -->
    <div class="article-info">
        <ul>
            <li id="author"><i class="iconfont icon-chuangzuozhejieshao"></i><?php echo esc_html( $author ); ?></li>
            <li id="category"><i class="iconfont icon-fenlei"></i><a href="<?php echo esc_url( $link ); ?>"
                                                                     title=" <?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></a>
            </li>
            <li>
                <i class="iconfont icon-shijian"></i><?php echo nicen_theme_timeToString( get_the_time( "Y-m-d H:i:s" ) ); ?>
            </li>
            <li><i class="iconfont icon-icon-test"></i><?php echo nicen_theme_getPostViews( get_the_ID() ); ?>热度</li>
            <li style="border:none"><i class="iconfont icon-pinglun"><a href="#comment"></i><?php echo get_comments_number(); ?>评论</a></li>
        </ul>
    </div>
	<?php
}