$(function () {
  const T = {
    /** 初始化 link 页面 */
    initForLinkPage() {
      const $linkList = $('.link-list:eq(0)');
      const $linkSearch = $('#linkSearch');

      if ($linkSearch.length === 0 && $linkList.length === 0) return;

      const getAllLinks = () => {
        const linksMap = new Map();
        const cateList = {};

        $linkList.each((idx, e) => {
          const $el = $(e);
          const cate = $el.find('h2').text();
          cateList[cate] = [];
          $el.find('ul li a').each((i, a) => {
            const $a = $(a);
            const item = {
              link_category: cate,
              link_name: $a.text().trim(),
              link_description: $a.data('desc') || '',
              link_url: $a.attr('href'),
              link_image: '',
            };
            cateList[cate].push(item);
            linksMap.set(item.link_url, item);
          });
        });

        return { links: [...linksMap.values()], cateList };
      };
      const linksInfo = getAllLinks();
      const linkListHtml = $linkList.html();
      let allLink = linksInfo.links;
      let timer;

      $linkSearch.off('input').on('input', () => {
        const keyword = $linkSearch.val().trim();
        const delay = allLink.length ? 100 : 300;

        clearTimeout(timer);
        timer = setTimeout(async () => {
          const oHtml = $linkList.html();
          let html = '';
          if (!keyword) {
            html = linkListHtml;
          } else {
            if (0 === allLink.length) {
              const r = await $.get('?json=1&orderby=rating&order=desc');
              if (Array.isArray(r)) allLink = r;
            }

            const list = allLink
              .filter(d => ['url', 'name', 'description'].some(k => String(d[`link_${k}`] || '').includes(keyword)))
              .map(item => {
                return [`<li><a href="${item.link_url}" title="${item.link_description}">${item.link_name}</a></li>`];
              });

            if (list.length > 0) html = `<li class="linkcat"><ul class='xoxo blogroll'>${list.join('\n')}</ul></li>`;
            else
              html = `<div class="flex center" style="font-size: 24px; font-weight: bold; text-align: center; padding: 50px">暂无匹配的结果</div>`;
          }

          if (html !== oHtml) {
            $linkList.html(html);
          }
        }, delay);
      });

      // ctrl+k 组合键聚焦搜索
      $(document).on('keydown', ev => {
        if (ev.ctrlKey && ev.key === 'k') {
          ev.preventDefault();
          $linkSearch.focus();
          return false;
        }
      });

    //   this.linkcatTip();
    },
  };

  T.initForLinkPage();
})();
