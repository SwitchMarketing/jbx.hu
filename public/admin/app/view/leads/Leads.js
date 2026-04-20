/**
 * This view is an example list of people.
 */
Ext.define('JBXAdmin.view.leads.Leads', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-leads',

    controller: 'leadscontroller',

    requires: [
        'JBXAdmin.store.LeadStore',
    ],

    title: 'Megkeresések',

    iconCls: 'x-fa fa-envelope',

    store: {
        type: 'leadstore'
    },

    platformConfig: {
        desktop: {
            columns: [
                { 
                    text: 'Név',
                    dataIndex: 'name',
                    flex : 1,
                    cell: {
                        userCls: 'bold'
                    }
                }, 
                {
                    text: 'Email',
                    width : 220,
                    dataIndex: 'email' 
                }, 
                { 
                    text: 'Telefon',
                    width : 150,
                    dataIndex: 'phone'
                },
                { 
                    text: 'Termékek',
                    flex : 1.5,
                    dataIndex: 'products'
                },
                { 
                    text: 'Forrás',
                    width : 100,
                    dataIndex: 'utm_source',
                    renderer : function (val) {
                        return val || '-';
                    }
                },
                { 
                    text: 'Megj.',
                    width : 80,
                    dataIndex: 'message_length',
                    align : 'center',
                    sortable : false,
                    renderer : function (val) {
                        return val;
                    }
                },
                { 
                    text: 'Létrehozva',
                    width : 150,
                    dataIndex: 'created_at'
                }
            ]
        },
        phone: {
            columns: [
                { 
                    text: 'Név',
                    dataIndex: 'name',
                    width : 150,
                    cell: {
                        userCls: 'bold'
                    }
                }, 
                {
                    text: 'Email',
                    width : 200,
                    dataIndex: 'email' 
                }, 
                { 
                    text: 'Telefon',
                    width : 150,
                    dataIndex: 'phone'
                },
                { 
                    text: 'Létrehozva',
                    width : 170,
                    dataIndex: 'created_at'
                }
            ]
        }
    },

    listeners: {
        select: 'onItemSelected'
    }
});
