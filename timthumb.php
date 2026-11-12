<?php
/*
 * TimThumb — 已停用（RETIRED）
 *
 * 主题缩略图已改用 WordPress 原生图片尺寸 API：
 *   - include/functions/common.php::nicen_get_img_timthumb
 *   - 尺寸复用 WordPress 内置的 medium / thumbnail（设置 → 媒体）
 * 主题不再生成任何指向本文件的 URL，旧的动态裁剪逻辑（含 shell_exec / 远程抓取，
 * 历史上有 SSRF、任意文件读取、命令注入漏洞）已整体移除以消除攻击面。
 *
 * 保留此文件仅为让历史缓存 / 伪静态链接得到明确的 410 响应，而非 500/404。
 * 确认无引用后可安全删除本文件；旧的 timthumb rewrite 规则亦可一并移除。
 */
http_response_code( 410 );
exit;
