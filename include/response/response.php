<?php
/*
 * 前端请求处理（WordPress admin-ajax.php）
 *
 * 本文件由 functions.php 在顶层 include，仅负责注册 AJAX 钩子，
 * 不再在每次请求时于文件作用域执行任何接口逻辑。
 *
 * 公开计数接口：wp_ajax_nopriv_* / wp_ajax_* + nonce + 频控
 * 后台管理接口：wp_ajax_*（仅登录）+ manage_options 权限 + nonce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 禁止直接访问
}


/**
 * 统一 JSON 响应（保留原有扁平字段结构，不改变前后端契约）。
 */
function nicen_theme_json( $data, $status = 200 ) {
	if ( ! headers_sent() ) {
		nocache_headers();
		header( 'X-Content-Type-Options: nosniff' );
	}
	wp_send_json( $data, $status ); // 自带 Content-Type: application/json 并终止请求
}


/**
 * 计数目标校验：仅允许对真实存在的文章/页面累加。
 */
function nicen_theme_valid_post( $id ) {
	$id = absint( $id );
	return ( $id > 0 && get_post( $id ) ) ? $id : 0;
}


/**
 * 频率控制（防脚本刷量）。指纹由用户 ID/IP/UA 组合而成，
 * 不信任 X-Forwarded-For 等可伪造请求头；命中窗口内重复请求时跳过累加。
 */
function nicen_theme_counter_throttled( $type, $post_id, $ttl ) {
	$uid = is_user_logged_in() ? 'u' . get_current_user_id() : 'anon';
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	$ua  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$key = 'nicen_ct_' . md5( $uid . '|' . $type . '|' . $post_id . '|' . $ip . '|' . $ua );

	if ( false !== get_transient( $key ) ) {
		return true;
	}
	set_transient( $key, 1, $ttl );

	return false;
}


/*
 * ------------------------------------------------------------------
 * 公开计数接口（点赞 / 点踩 / 阅读数）
 * ------------------------------------------------------------------
 */
function nicen_theme_ajax_counter() {

	// CSRF 防护：作用域专属 nonce（登录/未登录用户均可生成与校验）
	if ( ! check_ajax_referer( 'nicen_theme_counter', 'nonce', false ) ) {
		nicen_theme_json( [ 'code' => 0, 'errMsg' => '请求校验失败，请刷新页面' ], 403 );
	}

	$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
	$post_id = isset( $_POST['post_id'] ) ? nicen_theme_valid_post( $_POST['post_id'] ) : 0;

	if ( ! in_array( $type, [ 'view', 'nice', 'bad' ], true ) || ! $post_id ) {
		nicen_theme_json( [ 'code' => 0, 'errMsg' => '参数有误' ], 400 );
	}

	/*
	 * 点赞/点踩做服务端频控（防脚本刷量）；阅读数由前端 60 秒节流控制，
	 * 服务端不再额外写 transient，避免高流量站点每次浏览都产生一次数据库写入。
	 */
	switch ( $type ) {
		case 'nice':
			$throttled = nicen_theme_counter_throttled( 'nice', $post_id, MINUTE_IN_SECONDS );
			$count = $throttled ? nicen_theme_getPostNice( $post_id ) : nicen_theme_setPostNice( $post_id );
			nicen_theme_json( [
				'count'  => $count,
				'code'   => 1,
				'errMsg' => '点赞成功！'
			] );
			break;

		case 'bad':
			$throttled = nicen_theme_counter_throttled( 'bad', $post_id, MINUTE_IN_SECONDS );
			if ( ! $throttled ) {
				nicen_theme_setPostBad( $post_id );
			}
			nicen_theme_json( [
				'count'  => nicen_theme_getPostBad( $post_id ),
				'code'   => 1,
				'errMsg' => '踩成功！'
			] );
			break;

		case 'view':
		default:
			nicen_theme_setPostViews( $post_id );
			nicen_theme_json( [
				'code'   => 1,
				'errMsg' => '阅读+1！',
				'view'   => nicen_theme_getPostViews( $post_id )
			] );
			break;
	}
}
add_action( 'wp_ajax_nopriv_nicen_theme_counter', 'nicen_theme_ajax_counter' );
add_action( 'wp_ajax_nicen_theme_counter', 'nicen_theme_ajax_counter' );


/*
 * ------------------------------------------------------------------
 * 后台管理接口（仅登录用户，需 manage_options 权限 + nonce）
 * ------------------------------------------------------------------
 */
function nicen_theme_ajax_admin_guard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		nicen_theme_json( [ 'code' => 0, 'result' => '权限不足，请重新登录' ], 403 );
	}
	if ( ! check_ajax_referer( 'nicen_theme_admin', 'nonce', false ) ) {
		nicen_theme_json( [ 'code' => 0, 'result' => '安全校验失败，请刷新页面重试' ], 403 );
	}
}


/* 恢复默认配置 */
function nicen_theme_ajax_reset_default() {
	nicen_theme_ajax_admin_guard();

	foreach ( CONFIG as $key => $value ) {
		update_option( $key, $value );
	}

	nicen_theme_json( [
		'code'   => 1,
		'result' => '成功！'
	] );
}
add_action( 'wp_ajax_nicen_theme_reset_default', 'nicen_theme_ajax_reset_default' );


/* 生成 txt 站点地图 */
function nicen_theme_ajax_sitemap() {
	nicen_theme_ajax_admin_guard();

	// 写入站点根目录的绝对路径
	$sitemap = ABSPATH . 'sitemap.txt';

	if ( ! is_writable( ABSPATH ) ) {
		nicen_theme_json( [
			'code'   => 0,
			'result' => '根目录不可写，站点地图生成失败！'
		] );
	}

	if ( false !== file_put_contents( $sitemap, implode( "\n", nicen_theme_getLinks() ), LOCK_EX ) ) {
		nicen_theme_json( [
			'code'   => 1,
			'result' => '站点地图生成成功！'
		] );
	}

	nicen_theme_json( [
		'code'   => 0,
		'result' => '站点地图生成失败！'
	] );
}
add_action( 'wp_ajax_nicen_theme_sitemap', 'nicen_theme_ajax_sitemap' );


/* 百度站长链接提交 */
function nicen_theme_ajax_baidu_submit() {
	nicen_theme_ajax_admin_guard();

	$token = get_option( 'document_baidu' );

	if ( empty( $token ) ) {
		nicen_theme_json( [
			'code'   => 0,
			'result' => 'token尚未填写，无法进行推送！'
		] );
	}

	// 使用 add_query_arg 正确编码参数，避免 token 中的特殊字符破坏 URL
	$api = add_query_arg( [
		'site'  => site_url(),
		'token' => $token,
	], 'http://data.zz.baidu.com/urls' );

	$headers = [
		'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36',
		'Content-Type' => 'text/plain'
	];

	$links  = nicen_theme_getLinks();
	$result = '';
	$page   = 1;

	while ( true ) {
		$wait_submit = array_slice( $links, ( $page - 1 ) * 50, 50 );

		if ( empty( $wait_submit ) ) {
			break;
		}

		$res = wp_remote_post( $api, [
			'body'    => implode( "\n", $wait_submit ),
			'headers' => $headers,
			'timeout' => 120,
		] );

		$result .= wp_remote_retrieve_body( $res );
		$page ++;
	}

	nicen_theme_json( [
		'code'   => 1,
		'result' => $result
	] );
}
add_action( 'wp_ajax_nicen_theme_baidu_submit', 'nicen_theme_ajax_baidu_submit' );
