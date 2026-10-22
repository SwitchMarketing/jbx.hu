/**
 * Egy művelet = egy keskeny, színezett ikonoszlop a gridekben.
 *
 *     { xtype: 'jbx-actioncolumn', action: 'edit', tooltip: 'Szerkesztés', handler: 'onEditItem' }
 *
 * A handler szignatúrája ugyanaz, mint a cell.tools-nál: function (grid, info),
 * ahol info.record a sor rekordja. A szín az action-ből jön (jbx-action-<action>
 * osztály, lásd Main.scss).
 */
Ext.define('JBXAdmin.widget.ActionColumn', {
    extend: 'Ext.grid.column.Column',
    xtype: 'jbx-actioncolumn',

    isActionColumn: true,

    icons: {
        view   : 'x-fa fa-eye',
        edit   : 'x-fa fa-edit',
        image  : 'x-fa fa-image',
        list   : 'x-fa fa-list',
        delete : 'x-fa fa-trash'
    },

    text: '',
    width: 48,
    minWidth: 48,
    align: 'center',
    sortable: false,
    hideable: false,
    resizable: false,
    menuDisabled: true,

    constructor: function (config) {
        config = Ext.apply({}, config);

        var action = config.action || 'edit';

        config.cell = Ext.apply({
            cls: 'jbx-action-cell jbx-action-' + action,
            tools: {
                action: {
                    iconCls: config.iconCls || this.icons[action],
                    tooltip: config.tooltip,
                    handler: config.handler
                }
            }
        }, config.cell);

        delete config.action;
        delete config.iconCls;
        delete config.tooltip;
        delete config.handler;

        this.callParent([config]);
    }
});
