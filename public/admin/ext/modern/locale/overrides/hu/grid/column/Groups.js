Ext.define("Ext.locale.hu.grid.column.Groups", {
    override: "Ext.grid.column.Groups",

    config: {
        groupSummaryTpl: "Zusammenfassung ({name})",
        summaryTpl: "Zusammenfassung ({store.data.length})"
    },
    text: "Gruppen"
});
