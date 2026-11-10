<?php
/*
 * 处理所有前端请求
 *
 * 本文件由 functions.php 在顶层加载，所有接口仅在命中对应参数时执行。
 * 安全加固：密钥使用常量时间比较、计数目标校验、写入路径使用 ABSPATH、
 * 统一 JSON 响应头，且不改变原有的 code/result/count/view/errMsg 字段契约。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 禁止直接访问
}


/**
 * 统一输出 JSON 响应（带安全响应头）。
 * 保持原有字段结构不变，前后端调用契约不受影响。
 */
function nicen_theme_json( $data ) {
	if ( ! headers_sent() ) {
		nocache_headers(); // 计数/结果为动态内容，禁止缓存
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
		header( 'X-Content-Type-Options: nosniff' );
	}
	echo wp_json_encode( $data );
	exit;
}


/**
 * 读取请求参数（POST 优先，兼容历史 GET 调用）。
 * 数组等非标量一律视为无效，避免参数类型混淆与数组注入。
 */
function nicen_theme_request( $key ) {
	if ( isset( $_POST[ $key ] ) ) {
		$raw = $_POST[ $key ];
	} elseif ( isset( $_GET[ $key ] ) ) {
		$raw = $_GET[ $key ];
	} else {
		return null;
	}

	return is_scalar( $raw ) ? wp_unslash( $raw ) : null;
}


/**
 * 接口密钥校验。
 * 使用 hash_equals 做常量时间比较，替代原先的松散 != 比较，
 * 避免时序侧信道与字符串/数字类型混淆（如 0e 幻数）导致的绕过。
 */
function nicen_theme_auth() {
	$stored = (string) get_option( 'document_private' );

	// 兼容历史调用：密钥可来自 GET 或 POST，任一匹配即通过
	$candidates = [];
	foreach ( [ 'get', 'post' ] as $from ) {
		$source = $from === 'post' ? $_POST : $_GET;
		if ( isset( $source['private'] ) && is_scalar( $source['private'] ) ) {
			$value = (string) wp_unslash( $source['private'] );
			if ( $value !== '' ) {
				$candidates[] = $value;
			}
		}
	}

	if ( empty( $candidates ) ) {
		nicen_theme_json( [
			'code'   => 0,
			'result' => '密钥为空'
		] );
	}

	// 常量时间比较，避免时序侧信道与类型混淆；不允许空密钥
	$matched = false;
	if ( '' !== $stored ) {
		foreach ( $candidates as $candidate ) {
			if ( hash_equals( $stored, $candidate ) ) {
				$matched = true;
			}
		}
	}

	if ( ! $matched ) {
		nicen_theme_json( [
			'code'   => 0,
			'result' => '密钥有误'
		] );
	}
}


/**
 * 计数类接口的目标校验：仅允许对真实存在的文章/页面累加，
 * 防止对负数、小数、科学计数法或任意不存在的 ID 写入 post meta 造成数据污染。
 */
function nicen_theme_valid_post( $id ) {
	$id = absint( $id );
	return $id > 0 && get_post( $id ) ? $id : 0;
}


/*
 * 点赞
 * */
$nice = nicen_theme_request( 'document_nice' );
if ( null !== $nice && ( $post_id = nicen_theme_valid_post( $nice ) ) ) {
	$count = nicen_theme_setPostNice( $post_id );

	nicen_theme_json( [
		'count'  => $count,
		'code'   => 1,
		'errMsg' => '点赞成功！'
	] );
}


/*
 * 阅读数
 * */
$view = nicen_theme_request( 'document_view' );
if ( null !== $view && ( $post_id = nicen_theme_valid_post( $view ) ) ) {
	nicen_theme_setPostViews( $post_id );

	nicen_theme_json( [
		'code'   => 1,
		'errMsg' => '阅读+1！',
		'view'   => nicen_theme_getPostViews( $post_id )
	] );
}


/*
 * 踩
 * */
$bad = nicen_theme_request( 'document_bad' );
if ( null !== $bad && ( $post_id = nicen_theme_valid_post( $bad ) ) ) {
	nicen_theme_setPostBad( $post_id );

	nicen_theme_json( [
		'code'   => 1,
		'errMsg' => '踩成功！'
	] );
}


/*
 * 百度链接提交
 * */
if ( null !== nicen_theme_request( 'baidu_submit' ) ) {

	nicen_theme_auth(); // 权限验证

	$token = get_option( 'document_baidu' );

	/*
	 * 检查 token
	 * */
	if ( empty( $token ) ) {
		nicen_theme_json( [
			'code'   => 0,
			'result' => 'token尚未填写，无法进行推送！'
		] );
	}

	$site_url = site_url(); // 获取站点 url

	// 使用 add_query_arg 正确编码参数，避免 token 中的特殊字符破坏 URL
	$api = add_query_arg( [
		'site'  => $site_url,
		'token' => $token,
	], 'http://data.zz.baidu.com/urls' );

	/*
	 * 请求头模拟
	 * */
	$headers = [
		'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36',
		'Content-Type' => 'text/plain'
	];

	/*
	 * 请求数据
	 * */
	$links  = nicen_theme_getLinks();
	$result = ''; // 响应结果
	$page   = 1;  // 起始

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


/*
 * 链接遍历 / 生成 txt 站点地图
 * */
if ( null !== nicen_theme_request( 'sitemap' ) ) {

	nicen_theme_auth(); // 权限验证

	// 写入站点根目录的绝对路径，避免依赖当前工作目录的相对路径
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


/*
 * 恢复默认配置
 * */
if ( null !== nicen_theme_request( 'default' ) ) {
	nicen_theme_auth(); // 权限验证

	foreach ( CONFIG as $key => $value ) {
		// 接口密钥不随"恢复默认"重置，避免正在使用的密钥被改弱/失效
		if ( 'document_private' === $key ) {
			continue;
		}
		update_option( $key, $value );
	}

	nicen_theme_json( [
		'code'   => 1,
		'result' => '成功！'
	] );
}
