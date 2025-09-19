const tdStorKey = 'wp_document_stor';
const tdStorage = {
  get storkey() {
    return tdStorKey;
  },
  cache: JSON.parse(localStorage.getItem(tdStorKey) || '{}'),
  save() {
    localStorage.setItem(tdStorKey, JSON.stringify(this.cache));
  },
  getItem(key) {
    if (!key) return;
    return key.split('.').reduce((acc, cur) => (acc == undefined ? acc : acc[cur]), this.cache);
  },
  setItem(key, value) {
    if (!key) return;
    if (typeof key === 'object') {
      this.cache = key;
    } else {
      key.split('.').reduce((acc, cur, index, arr) => {
        if (index === arr.length - 1) {
          acc[cur] = value;
        } else {
          if (typeof acc[cur] != 'object') acc[cur] = {};
          return acc[cur];
        }
      }, this.cache);
    }

    this.save();
  },
  removeItem(key) {
    let ok = false;
    if (!key) return ok;
    key.split('.').reduce((acc, cur, index, arr) => {
      if (acc && index === arr.length - 1) {
        ok = true;
        delete acc[cur];
      } else if (acc) {
        return acc[cur];
      }
    }, this.cache);

    if (ok) this.save();
    return ok;
  },
};
/** 全局对象 */
const TD = {
  config: {
    cdn: {
      sweetalert2: [
        {
          u: 'https://s4.zstatic.net/ajax/libs/sweetalert2/11.16.1/sweetalert2.min.css',
          integrity: 'sha512-WnmDqbbAeHb7Put2nIAp7KNlnMup0FXVviOctducz1omuXB/hHK3s2vd3QLffK/CvvFUKrpioxdo+/Jo3k/xIw==',
        },
        {
          u: 'https://s4.zstatic.net/ajax/libs/sweetalert2/11.16.1/sweetalert2.min.js',
          integrity: 'sha512-LGHBR+kJ5jZSIzhhdfytPoEHzgaYuTRifq9g5l6ja6/k9NAOsAi5dQh4zQF6JIRB8cAYxTRedERUF+97/KuivQ==',
        },
      ],
    },
  },
  /** {@see https://sweetalert2.github.io/} */
  alert(msg, p) {
    p = typeof msg === 'object' ? msg : Object.assign({ text: msg }, p);
    return this.loadJsOrCss('sweetalert2').then(() =>
      Swal.fire(
        Object.assign(
          { icon: 'info', showConfirmButton: false, showCloseButton: true, confirmButtonText: '确定', cancelButtonText: '关闭' },
          p
        )
      )
    );
  },
  toast(msg, p) {
    p = typeof msg === 'object' ? msg : Object.assign({ text: msg }, p);
    return this.alert(Object.assign({ toast: true, position: 'top-end', icon: 'success', timer: 2000, timerProgressBar: true }, p));
  },
  async loadJsOrCss(urls = [], options = {}) {
    if (typeof urls == 'string') urls = [urls];
    const list = [];
    for (let url of urls) {
      if (this.config.cdn[url]) url = this.config.cdn[url];
      if (Array.isArray(url)) {
        await this.loadJsOrCss(url);
        continue;
      }

      let opts = Object.assign({}, options);

      if (typeof url === 'object') {
        opts = Object.assign({}, opts, url);
        url = url.src || url.href || url.url || url.u;
      }

      const isCss = url.includes('.css');
      const isLoaded = document.querySelector(isCss ? `link[href="${url}"]` : `script[src="${url}"]`);
      if (isLoaded) continue;

      const el = document.createElement(isCss ? 'link' : 'script');
      if (opts.attr) Object.entries(opts.attr).forEach(d => el.setAttribute(d[0], d[1]));

      if (opts.integrity && !opts.crossOrigin) opts.crossOrigin = 'anonymous';
      ['crossOrigin', 'integrity', 'id'].forEach(k => opts[k] && (el[k] = opts[k]));

      if (isCss) {
        el.rel = 'stylesheet';
        el.href = url;
      } else {
        el.type = opts.type || 'text/javascript';
        if (opts.async) el.async = true;
        if (opts.defer) el.defer = true;
        el.src = url;
      }

      const p = new Promise(rs => {
        el.onload = () => rs(el);
        setTimeout(() => rs(el), 5_000);
        document.querySelector('head').append(el);
      });
      list.push(p);
    }

    return Promise.allSettled(list);
  },
  /** 更新适配系统自带小工具的样式 */
  updateWidgetStyle() {
    $('aside .widget').each(function () {
      if ($(this).hasClass('div-info')) return;

      $(this).addClass('div-info').css('display', 'block');
      const title = $(this).find('h2').hide().text();
      $(this).prepend(`<div class="header"><ul><li class="active"><div class="mark"></div>${title}</li></ul></div>`);
    });
  },
  onReady() {
    if (window.h5Utils && h5Utils.config) Object.assign(TD.config.cdn, h5Utils.config.cdn);
    this.updateWidgetStyle();
  },
  async init() {
    $(function () {
        TD.onReady();
    })();
  }
};

TD.init();

/*
 * 切换主题皮肤
 * */
function toggleTheme(flag = true) {
  if (flag) {
    //暗黑主题
    $('html').addClass('dark').removeClass('personal');
    //标记暗黑模式
    localStorage.setItem('night', 1);
    //改变图标
    $(function () {
      $('.read-mode i').removeClass('icon-baitian-qing').addClass('icon-yueliang');
    });
  } else {
    //暗黑主题
    $('html').removeClass('dark').addClass('personal');
    //移除暗黑模式标记
    localStorage.removeItem('night');
    //改变图标
    $(function () {
      $('.read-mode i').removeClass('icon-yueliang').addClass('icon-baitian-qing');
    });
  }
}

/*
 * 动态rem
 * */
let calcRem = () => {
  const r = document.documentElement;
  let o = r.offsetWidth / 100;
  o = Math.min(Math.max(o, 17), 22); // 17 ~ 22
  r.style.fontSize = o + 'px';
  window.rem = o;
};
window.addEventListener('resize', calcRem);
calcRem();

/*同步主题*/
let theme = localStorage.getItem('theme-color');
if (!!theme) {
  $('html').addClass(theme);
}
/*同步阅读模式 */
let night = localStorage.getItem('night');

/*
 * 是否需要切换模式
 * */
if (!!night) {
  toggleTheme(true); //切换暗黑
}

/*
 * 获取元素在网页的实际top
 * */
$.fn.getTop = function () {
  let position = this.position();
  /*
   * 为0代表有很多offsetTop要计算
   * */
  if (position.top !== 0) {
    return position.top;
  } else {
    let html = $('html').get(0);
    return this.get(0).getBoundingClientRect().top + html.scrollTop;
  }
};

/*jq内存清理函数*/
$.fn.removeWithLeakage = function () {
  this.each(function (i, e) {
    $('*', e)
      .add([e])
      .each(function () {
        $.event.remove(this);
        $.removeData(this);
      });
    if (e.parentNode) e.parentNode.removeChild(e);
  });
};

/** toast */
function toast(msg, options = {}) {
  TD.toast(msg, options);
}
