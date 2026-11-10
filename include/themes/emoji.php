<?php
/*
* @author 友人a丶
* @date 2022/10/17
* 评论时转换表情为链接
*/


/**
 * @param $comment_ID
 *
 * 评论内提取表情
 */
function nicen_theme_textToEmoji( $comment_ID ) {

    global $wpdb;

    $comment = get_comment( $comment_ID );
    if ( ! $comment ) {
        return;
    }
    $html    = $comment->comment_content;
    $path    = get_cdn_uri() . "/assets/smilies/"; //主题目录
    $dir     = get_template_directory() . "/assets/smilies/"; //主题目录

    /*
     * 匹配是否有表情（名称仅允许字母数字、下划线、连字符，避免正则注入与路径穿越）
     * */
    if ( preg_match_all( "/:([A-Za-z0-9_-]+):/", $html, $match ) ) {

        $count = 0; //有效表情数量

        foreach ( array_unique( $match[1] ) as $emoji ) {
            // 二次确认文件存在，且严格限制名称，杜绝 ../ 穿越
            if ( preg_match( '/^[A-Za-z0-9_-]+$/', $emoji ) && is_file( $dir . $emoji . ".png" ) ) {
                $img  = "<img class='comments-emoji' src='" . esc_url( $path . $emoji . ".png" ) . "' title='emoji'/>";
                $html = str_replace( ':' . $emoji . ':', $img, $html );
                $count ++;
            }
        }

        /*
         * 有替换才回写，且使用 $wpdb->update 参数化
         * */
        if ( $count > 0 ) {
            $wpdb->update(
                $wpdb->comments,
                [ 'comment_content' => $html ],
                [ 'comment_ID' => (int) $comment_ID ],
                [ '%s' ],
                [ '%d' ]
            );
        }
    }
}


add_action( 'comment_post', 'nicen_theme_textToEmoji' );
