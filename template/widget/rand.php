<?php

/*
 * 侧边栏最新文章
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
    <div class="div-info <?php echo $show ? 'recent' : ''; ?>">
        <div class="header">
            <ul>
                <li class="active"><div class="mark"></div><?php echo esc_html($title); ?></li>
                <!-- <li>修改记录</li>-->
            </ul>
        </div>
        <ul class="ul">

		<?php
		/*
		 * 判断显示类型
		 * */
		switch ( intval( $type ) ) {
			case 1:
				query_posts( "showposts=" . $number );
				break;
			case 2:
				query_posts( "showposts=" . $number . "&orderby=modified" );
				break;
			case 3:
				query_posts( "posts_per_page=" . $number . "&orderby=rand" );
				break;
			case 4:
				// LIMIT 使用 %d 参数化，meta_key 使用 %s
				$sql    = $wpdb->prepare(
					"SELECT `post_id`,`meta_value` FROM {$wpdb->postmeta} WHERE `meta_key` = %s ORDER BY `meta_value`+0 DESC LIMIT %d",
					'views',
					$number
				);
				$result = $wpdb->get_results( $sql, ARRAY_A );

				$post_ids = array_map( fn( $item ) => (int) $item['post_id'], $result );

				query_posts( [
						"post__in"  => $post_ids,
						'showposts' => $number
					]
				);
				break;
			default:
				query_posts( "showposts=" . $number );
				break;
		}

		while ( have_posts() ) {
			the_post();
			$permalink = get_the_permalink();
			$title_txt = get_the_title();
			$thumb     = nicen_get_img_timthumb( null, 78, 56 );
			if ( $show ) {
				?>
                <li>
                    <a href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_attr( $title_txt ); ?>" target="_blank" rel="noopener noreferrer">
                        <div class="thumnbnail">
                            <img loading="lazy" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title_txt ); ?>" />
                        </div>
                        <div class="article">
                            <div class="caption">
                            <?php echo esc_html( $title_txt ); ?>
                            </div>
                            <div class="datetime">
                            <?php echo esc_html( nicen_theme_timeToString( get_the_time( "Y-m-d H:i:s" ) ) ); ?>
                            </div>
                        </div>
                    </a>
                </li>
				<?php
			} else {
				?>
                <li>
                    <div class="title">
                        <a href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_attr( $title_txt ); ?>"><i class="iconfont icon-xiangxiazhankai1"></i><?php echo esc_html( $title_txt ); ?></a>
                    </div>
                </li>
				<?php
			}
		}
		wp_reset_query(); //重置文章指指针
		?>
    </ul>

</div>
