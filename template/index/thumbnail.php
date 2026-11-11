<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$thumbnail = nicen_theme_getThumbnail(); //是否显示


/*
 * 判断缩略图显示的位置
 * */
if ( nicen_theme_config( 'document_thumbnail_position', false ) == "right" ) {
	$position = ""; //默认为空
} else {
	$position = "i-article-left"; //默认为空
}


if ( $thumbnail ) { ?>
    <div class="i-article-thumb <?php echo esc_attr( $position ); ?>">
        <a href="<?php echo esc_url( get_the_permalink() ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( get_the_title() ); ?>">
            <img loading="lazy" src="<?php echo esc_url( nicen_get_img_timthumb($thumbnail, 220, 140) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>"/>
        </a>
    </div>
<?php } ?>