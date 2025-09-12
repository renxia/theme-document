if (window._ts) {
    /* 获取当前时间的时间戳（单位：秒） */
    const currentTime = Math.floor(Date.now() / 1000);
    /* 计算时间差（单位：秒） */
    const timeDiff = currentTime - window._ts;
    /* 判断时间差是否超过 60 秒 */
    if (timeDiff > 60) {
        $.post(location.pathname + "?document_view=" + Current, function (res) {
            const data = JSON.parse(res);
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
