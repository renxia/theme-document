<?php


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
    <div class="i-article-thumb <?php echo $position ?>">
        <a href="<?php the_permalink(); ?>" target="_blank" title="<?php the_title(); ?>">
            <img loading="lazy" src="<?php echo nicen_get_img_timthumb($thumbnail, 220, 140) ?>" alt="<?php the_title(); ?>"/>
        </a>
    </div>
<?php } ?>