/**
 * Súgó: kezelői kézikönyv. Bal oldalon a fejezetek (resources/help/index.json),
 * jobb oldalon a kiválasztott fejezet Markdownból renderelve.
 */
Ext.define('JBXAdmin.view.help.Help', {
    extend: 'Ext.Panel',
    xtype: 'app-help',

    controller: 'helpcontroller',

    title: 'Súgó',

    layout: 'hbox',

    items: [
        {
            xtype: 'list',
            reference: 'chapterList',
            width: 280,
            cls: 'jbx-help-nav',
            itemTpl: '{title}',
            store: {
                fields: ['id', 'title'],
                data: []
            },
            listeners: {
                select: 'onChapterSelect'
            }
        },
        {
            xtype: 'component',
            reference: 'chapterBody',
            flex: 1,
            scrollable: true,
            cls: 'jbx-help',
            html: ''
        }
    ]
});
