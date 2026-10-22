Ext.define('JBXAdmin.view.help.HelpController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.helpcontroller',

    /** A súgó tartalma (a Sencha build a resources/ mappát változatlanul kimásolja) */
    basePath: 'resources/help/',

    init: function () {
        var me = this;

        me.lookup('chapterBody').element.on('click', me.onBodyClick, me);

        // statikus fájlok: fetch (nem Ext.Ajax), hogy a globális Ajax hiba-alert neszóljon közbe
        me.fetchText('index.json').then(function (text) {
            var chapters = Ext.decode(text),
                list = me.lookup('chapterList');

            list.getStore().setData(chapters);
            if (chapters.length) {
                list.select(0);
            }
        }).catch(function () {
            me.showError('A súgó tartalomjegyzéke nem tölthető be.');
        });
    },

    /** Szöveges fájl a súgó mappából; nem 2xx válasz hiba */
    fetchText: function (file) {
        return fetch(this.basePath + file, { cache: 'no-cache' }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        });
    },

    onChapterSelect: function (list, selected) {
        var record = Ext.isArray(selected) ? selected[0] : selected;

        if (record) {
            this.loadChapter(record.get('id'));
        }
    },

    loadChapter: function (id) {
        var me = this,
            body = me.lookup('chapterBody');

        me.fetchText(id + '.md').then(function (text) {
            var scroller;

            body.setHtml(me.renderMarkdown(text));
            scroller = body.getScrollable();
            if (scroller) {
                scroller.scrollTo(0, 0);
            }
        }).catch(function () {
            me.showError('A fejezet nem tölthető be.');
        });
    },

    /** Markdown -> HTML; a relatív képútvonalak a súgó mappájához képest értendők */
    renderMarkdown: function (text) {
        if (!window.marked) {
            return '<pre>' + Ext.String.htmlEncode(text) + '</pre>';
        }

        return window.marked.parse(text)
            .replace(/(<img[^>]*\ssrc=")(?!https?:|\/)([^"]+)"/g, '$1' + this.basePath + '$2"');
    },

    /** Fejezetek közti link a súgón belül vált; kép kattintásra eredeti méretben nyílik */
    onBodyClick: function (e) {
        var link = e.getTarget('a'),
            img = e.getTarget('img'),
            href, match, list, record;

        if (img && !link) {
            window.open(img.src, '_blank', 'noopener');
            return;
        }

        if (!link) {
            return;
        }

        href = link.getAttribute('href') || '';
        match = href.match(/^([\w-]+)\.md(?:#.*)?$/);

        if (match) {
            e.preventDefault();
            list = this.lookup('chapterList');
            record = list.getStore().getById(match[1]);
            if (record) {
                list.select(record);
            }
        } else if (/^https?:/.test(href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener');
        }
    },

    showError: function (message) {
        this.lookup('chapterBody').setHtml('<p class="jbx-help-error">' + Ext.String.htmlEncode(message) + '</p>');
    }
});
