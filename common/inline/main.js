const tdStorKey = 'wp_document_stor';
const tdStorage = {
    get key() { return tdStorKey },
    cache: JSON.parse(localStorage.getItem(tdStorKey) || '{}'),
    save() {
        localStorage.setItem(this.key, JSON.stringify(this.cache));
    },
    getItem(key) {
        if (!key) return;
        return key.split('.').reduce((acc, cur) => acc == undefined ? acc : acc[cur], this.cache);
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
    }
};


/*
* 切换主题皮肤
* */
function toggleTheme(flag = true) {
    if (flag) {
        //暗黑主题
        $('html')
            .addClass('dark')
            .removeClass('personal');
        //标记暗黑模式
        localStorage.setItem('night', 1);
        //改变图标
        $(function () {
            $('.read-mode i')
                .removeClass("icon-baitian-qing")
                .addClass("icon-yueliang");
        });
    } else {
        //暗黑主题
        $('html')
            .removeClass('dark')
            .addClass('personal');
        //移除暗黑模式标记
        localStorage.removeItem('night');
        //改变图标
        $(function () {
            $('.read-mode i')
                .removeClass("icon-yueliang")
                .addClass("icon-baitian-qing");
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
    r.style.fontSize = o + "px";
    window.rem = o;
};
window.addEventListener('resize', calcRem);
calcRem();

/*同步主题*/
let theme = localStorage.getItem('theme-color');
if (!!theme) {
    $('html').addClass(theme)
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
}


/*jq内存清理函数*/
$.fn.removeWithLeakage = function () {
    this.each(function (i, e) {
        $("*", e).add([e]).each(function () {
            $.event.remove(this);
            $.removeData(this);
        });
        if (e.parentNode)
            e.parentNode.removeChild(e);
    });
};

/** toast */
function toast(msg, options = {}) {
    if (window.h5Utils) window.h5Utils.toast(msg, options);
}
