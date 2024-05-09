/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
Ext.application({
    extend: 'JBXAdmin.Application',

    name: 'JBXAdmin',

    requires: [
        // This will automatically load all classes in the JBXAdmin namespace
        // so that application classes do not need to require each other.
        'JBXAdmin.*'
    ],

    // The name of the initial view to create.
    // mainView: 'JBXAdmin.view.main.Main'
});
