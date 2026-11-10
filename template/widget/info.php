<?php
/*
 * 文章信息样式
 * @author 友人a丶
 * @date 2022-07-08
 * */
global $post;
$author = get_the_author_meta( 'display_name', $post->post_author );
if ( is_singular() ) {
	?>
    <!--文章信息-->
    <div class="div-info widget-article-info">
        <div class="header">
            <ul>
                <li class="active"><div class="mark"></div>文章信息</li>
                <!-- <li>修改记录</li>-->
            </ul>
        </div>
        <ul class="ul" style="margin-top: 0.5rem;">
            <li><?php echo esc_html( $author ); ?>创建于：<span><?php echo esc_html( get_the_time( "Y-m-d H:i" ) ); ?></span></li>
            <li>最后编辑于：<span><?php echo esc_html( get_new_post_modified_time( "Y-m-d H:i" ) ); ?></span></li>
            <li class="read-time-info">字数统计：<span><?php
                // 兼容未安装 mbstring 扩展的环境（PHP 7.4 / 8.5 均可回退）
                $content = strip_tags( get_the_content() );
                echo esc_html( function_exists( 'mb_strlen' ) ? mb_strlen( $content ) : strlen( $content ) );
            ?></span></li>
        </ul>
    </div>
	<?php
}
?>