/**
 * Orders list view
 */
Ext.define('JBXAdmin.view.orders.Orders', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-orders',

    controller: 'orderscontroller',

    requires: [
        'JBXAdmin.view.orders.OrdersController',
        'JBXAdmin.store.OrderStore',
    ],

    title: 'Megrendelések',

    store: {
        type: 'orderstore'
    },

    platformConfig: {
        desktop: {
            columns: [
                { 
                    text: 'ID',
                    dataIndex: 'id',
                    width: 60,
                    cell: {
                        userCls: 'bold'
                    }
                },
                { 
                    text: 'Név',
                    dataIndex: 'customer_name',
                    flex: 1,
                    cell: {
                        userCls: 'bold'
                    }
                }, 
                {
                    text: 'Email',
                    width: 220,
                    dataIndex: 'email' 
                }, 
                { 
                    text: 'Telefon',
                    width: 130,
                    dataIndex: 'phone'
                },
                { 
                    text: 'Cég',
                    flex: 1,
                    dataIndex: 'company'
                },
                { 
                    text: 'Adószám',
                    width: 120,
                    dataIndex: 'tax_number'
                },
                { 
                    text: 'Létrehozva',
                    width: 150,
                    dataIndex: 'created_at'
                },
                { 
                    text: 'Kiküldve',
                    width: 150,
                    dataIndex: 'emailed_at',
                    renderer: function(val) {
                        return val || '-';
                    }
                }
            ]
        },
        phone: {
            columns: [
                { 
                    text: 'ID',
                    dataIndex: 'id',
                    width: 50
                },
                { 
                    text: 'Név',
                    dataIndex: 'customer_name',
                    width: 150,
                    cell: {
                        userCls: 'bold'
                    }
                }, 
                {
                    text: 'Email',
                    width: 200,
                    dataIndex: 'email' 
                }, 
                { 
                    text: 'Létrehozva',
                    width: 170,
                    dataIndex: 'created_at'
                }
            ]
        }
    },

    listeners: {
        select: 'onItemSelected'
    }
});
