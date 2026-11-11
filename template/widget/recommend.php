<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * 文章推荐
 * @author 友人a丶
 * @date 2022-07-08
 * */
    global $wpdb;

    // 数量统一转为非负整数，防止 widget 配置值拼接进 SQL / query_posts
    $number = absint($number);
    if ($number < 1) {
        $number = 5;
    }
?>
<div class="div-info recommend <?php echo esc_attr($show_title == 1 ? '' : 'no-title'); ?>">

	<?php if ( $show_title == 1 ) { ?>

        <div class="header">
            <ul>
                <li class="active"><div class="mark"></div><?php echo esc_html($title); ?></li>
            </ul>
        </div>
	<?php } ?>

    <div class="list" id="swiper">
        <div class="swiper-wrapper">
			<?php
			/*
			 * 判断显示类型
			 * */

			$condition = [ 'showposts' => $number ];

			$count = 0; //加载的文章数量

			// 分类 ID 统一整数化，供 IN 子句使用
			$category_ids = [];

			if ( is_single() ) {

				/* 获取目录 */
				$cat = get_the_category( get_the_ID() );

				/* 条件 */
				if ( $cat ) {
					foreach ( $cat as $term ) {
						$category_ids[] = (int) $term->term_id;
					}

					$condition = array_merge( $condition, [
						'tax_query' => [
							[
								'taxonomy' => 'category',
								'field'    => 'term_id',
								'terms'    => $category_ids,
							],
						]
					] );
				}
			}


			switch ( intval( $type ) ) {
				case 1:
					query_posts( $condition );
					break;
				case 2:
					$condition = array_merge( $condition, [ "orderby" => "modified" ] );
					query_posts( $condition );
					break;
				case 3:
					$condition = array_merge( $condition, [
						'posts_per_page' => $number,
						"orderby"        => "rand"
					] );
					query_posts( $condition );
					break;
				case 4:
					// IN 子句用 %d 占位符逐项构造，LIMIT 用 %d，避免任何拼接注入
					if ( is_single() && ! empty( $category_ids ) ) {
						$placeholders = implode( ',', array_fill( 0, count( $category_ids ), '%d' ) );
						$sql = $wpdb->prepare(
							"SELECT a.post_id, a.meta_value
							 FROM {$wpdb->postmeta} AS a
							 JOIN {$wpdb->term_relationships} AS b ON a.post_id = b.object_id
							 WHERE a.meta_key = %s
							   AND b.term_taxonomy_id IN ($placeholders)
							 ORDER BY a.meta_value+0 DESC LIMIT %d",
							array_merge( [ 'views' ], $category_ids, [ $number ] )
						);
					} else {
						$sql = $wpdb->prepare(
							"SELECT a.post_id, a.meta_value
							 FROM {$wpdb->postmeta} AS a
							 JOIN {$wpdb->term_relationships} AS b ON a.post_id = b.object_id
							 WHERE a.meta_key = %s
							 ORDER BY a.meta_value+0 DESC LIMIT %d",
							'views',
							$number
						);
					}

					$result   = $wpdb->get_results( $sql, ARRAY_A );
					$post_ids = array_map( function ( $item ) {
						return (int) $item['post_id'];
					}, $result );

					query_posts( array_merge( $condition, [ "post__in" => $post_ids ] ) );
					break;
				default:
					query_posts( $condition );
					break;
			}

			/*
			 * 递归显示文章
			 * */

			while ( have_posts() ) {
				$count ++;
				the_post();
				?>

                <div class="swiper-slide">
                    <a href="<?php echo esc_url(get_the_permalink()) ?>" title="<?php echo esc_attr(get_the_title()) ?>"
                       target="_blank" rel="noopener noreferrer">
                        <div class="thumnbnail">
                            <img loading="lazy" src="<?php echo esc_url(nicen_get_img_timthumb(null, 78, 56)) ?>" alt="<?php echo esc_attr(get_the_title()) ?>"/>
                        </div>
                        <div class="caption">
							<?php echo esc_html(get_the_title()) ?>
                        </div>
                        <div class="detail">
                            <span><?php echo esc_html(get_the_time( "Y年m月d日" )) ?></span>
                            <span>
                                        <i class="iconfont icon-icon-test"></i><?php echo esc_html(nicen_theme_getPostViews( get_the_ID() )); ?>
                                    </span>
                        </div>
                        <div class="category">
							<?php echo esc_html(nicen_theme_getCategory( get_the_ID() )); ?>
                        </div>
                    </a>
                </div>

				<?php
			}
			wp_reset_query(); //重置文章指指针
			?>
        </div>
		<?php

		if ( $count == 0 ) {
			$url = get_cdn_uri() . "/assets/images/nothing.svg"; //主题url
			echo '<div class="empty">
                            <img src="' . esc_url($url) . '" title="暂无相关文章" />
                            <span>暂无可推荐内容</span>
                      </div>';
		} else {
			echo '
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>';
		}
		?>
    </div>
</div>
