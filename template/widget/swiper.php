<?php

/*
 * 首页轮播导航
 * @author 友人a丶
 * @date 2022-07-08
 * */
    $default = get_cdn_uri() . "/assets/images/default.png";

?>
<div class="swiper mySwiper <?php echo esc_attr( $show_in_mobile ? '' : 'mobile' ); ?>">
    <div class="swiper-wrapper">
        <div class="swiper-slide">
			<?php

			for ( $i = 0; $i < 3; $i ++ ) {
				$class = '';
				switch ( $i ) {
					case 0:
						$class = "swiper-a";
						break;
					case 1:
						$class = "swiper-b";
						break;
					case 2:
						$class = "swiper-c";
						break;
				}
				$item_link     = isset( $link[ $i ] ) ? $link[ $i ] : '';
				$item_title    = isset( $title[ $i ] ) ? $title[ $i ] : '';
				$item_category = isset( $category[ $i ] ) ? $category[ $i ] : '';
				$item_datetime = isset( $datetime[ $i ] ) ? $datetime[ $i ] : '';
				$item_number   = isset( $number[ $i ] ) ? $number[ $i ] : '';
				$item_thumb    = ( isset( $thumbnail[ $i ] ) && ! empty( $thumbnail[ $i ] ) ) ? $thumbnail[ $i ] : $default;
				?>
                <div class="swiper-item <?php echo esc_attr( $class ); ?>">
                    <a href="<?php echo esc_url( $item_link ); ?>" title="<?php echo esc_attr( $item_title ); ?>" target="_blank"
                       rel="noopener noreferrer" class="modal">
                        <span class="category"><?php echo esc_html( $item_category ); ?></span>
                        <div class="bottom">
                            <div class="title"><?php echo esc_html( $item_title ); ?></div>

							<?php
							if ( ! empty( $item_datetime ) ) {
								echo ' <span>' . esc_html( $item_datetime ) . '</span>';
							}
							if ( ! empty( $item_number ) ) {
								echo ' <span><i class="iconfont icon-icon-test"></i>' . esc_html( $item_number ) . '</span>';
							}
							?>

                        </div>
                    </a>
                    <img loading="lazy" src="<?php echo esc_url( $item_thumb ); ?>"/>
                </div>
				<?php
			}
			?>
        </div>
    </div>
</div>
