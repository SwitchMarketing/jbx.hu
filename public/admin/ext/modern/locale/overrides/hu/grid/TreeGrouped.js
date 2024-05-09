Ext.define("Ext.locale.hu.grid.TreeGrouped", {
    override: "Ext.grid.TreeGrouped",

    config: {
        groupSummaryTpl: "Zusammenfassung ({name})",
        summaryTpl: "Zusammenfassung ({store.data.length})"
    }
});
