if (window._ts) {
    /* 获取当前时间的时间戳（单位：秒） */
    const currentTime = Math.floor(Date.now() / 1000);
    /* 计算时间差（单位：秒） */
    const timeDiff = currentTime - window._ts;
    /* 判断时间差是否超过 60 秒 */
    if (timeDiff > 60 && window.nicenTheme && typeof Current !== 'undefined') {
        // 迁移到 admin-ajax.php：显式 action + nonce，后端做频控与校验
        $.post(window.nicenTheme.ajaxUrl, {
            action: 'nicen_theme_counter',
            type: 'view',
            post_id: Current,
            nonce: window.nicenTheme.counterNonce
        }, function (res) {
            // 后端返回 application/json，jQuery 已自动解析为对象；兼容仍为字符串的情况
            const data = (typeof res === 'string') ? JSON.parse(res) : res;
            $(() => {
                $("div.article-info > ul > li:nth-child(4)").html(`<i class="iconfont icon-icon-test"></i>${data.view}热度`)
            })
        });
    }
}

if (window.TD && window.ROOT) {
  /** 更新阅读时间 */
  function updateReadingTime() {
    const $readTime = $('.read-time-info');
    if (!$readTime.length) return;

    const text = $('.main-article').text();
    if (!text.length) return;

    TD.loadJsOrCss(`${window.ROOT}/common/reading-time/reading-time.js`).then(() => {
      const { words, minutes } = readingTime(text.trim(), { wordsPerMinute: 200 });
      $readTime.html(`<i class="iconfont icon-icon-test"></i>字数 ${words}，阅读大约需 ${minutes} 分钟`);
    });
  }

  updateReadingTime();
}
