Ext.define('JBXAdmin.widget.StateToggle', {
    extend: 'Ext.Button',
    xtype: 'jbx-statetoggle',

    defaultBindProperty: 'activeState',

    config: {
        activeState: null
    },

    updateActiveState: function(state) {
        var isActive = state === 'active';
        this.setIconCls(isActive ? 'x-fa fa-check-circle' : 'x-fa fa-times-circle');
        this.removeCls(['jbx-state-active', 'jbx-state-inactive']);
        this.addCls(isActive ? 'jbx-state-active' : 'jbx-state-inactive');
    }
});
