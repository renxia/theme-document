<?php
/*
 * 输出文章的结构化数据
 *
 * 使用 wp_json_encode 构造 JSON-LD，标题/摘要中的引号与 </script> 均被安全转义，
 * 避免污染 JSON 或注入脚本。
 * */
?>
<script type="application/ld+json">
<?php
$data = [
    '@context' => [
        'images' => [
            '@id'    => 'http://schema.org/image',
            '@type'  => '@id',
            '@container' => '@list',
        ],
        'title' => 'http://schema.org/headline',
    ],
    '@id'         => get_the_permalink(),
    'title'       => get_the_title(),
    'images'      => [ nicen_theme_getThumb() ],
    'description' => nicen_theme_getExcerpt( get_the_excerpt(), $post->post_password, false ),
    'pubDate'     => get_the_time( 'Y-m-d H:i:s' ),
    'upDate'      => get_new_post_modified_time( 'Y-m-d H:i:s' ),
];

echo wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
?>
</script>
