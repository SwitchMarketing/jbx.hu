/**
 * Hungarian translation
 */
Ext.onReady(function () {
  if (Ext.Date) {
    Ext.Date.monthNames = [
      "Január",
      "Február",
      "Március",
      "Április",
      "Május",
      "Június",
      "Július",
      "Augusztus",
      "Szeptember",
      "Október",
      "November",
      "December",
    ];

    Ext.Date.defaultFormat = "d.m.Y";
    Ext.Date.defaultTimeFormat = "H:i";

    Ext.Date.getShortMonthName = function (month) {
      return Ext.Date.monthNames[month].substring(0, 3);
    };

    Ext.Date.monthNumbers = {
      Jan: 0,
      Feb: 1,
      Már: 2,
      Ápr: 3,
      Máj: 4,
      Jún: 5,
      Júl: 6,
      Aug: 7,
      Szep: 8,
      Okt: 9,
      Nov: 10,
      Dec: 11,
    };

    Ext.Date.getMonthNumber = function (name) {
      return Ext.Date.monthNumbers[
        name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()
      ];
    };

    Ext.Date.dayNames = [
      "Vasárnap",
      "Hétfő",
      "Kedd",
      "Szerda",
      "Csütörtök",
      "Péntek",
      "Szombat",
    ];

    Ext.Date.getShortDayName = function (day) {
      return Ext.Date.dayNames[day].substring(0, 3);
    };
  }

  if (Ext.util && Ext.util.Format) {
    Ext.util.Format.__number = Ext.util.Format.number;

    Ext.util.Format.number = function (v, format) {
      return Ext.util.Format.__number(v, format || "0.000,00/i");
    };

    Ext.apply(Ext.util.Format, {
      thousandSeparator: ".",
      decimalSeparator: ",",
      currencySign: "€", // Euró
      dateFormat: "d.m.Y",
    });
  }
});

Ext.define("Ext.locale.hu.Panel", {
  override: "Ext.Panel",

  config: {
    standardButtons: {
      ok: {
        text: "OK",
      },
      abort: {
        text: "Megszakít",
      },
      retry: {
        text: "Újrapróbál",
      },
      ignore: {
        text: "Figyelmen kívül hagy",
      },
      yes: {
        text: "Igen",
      },
      no: {
        text: "Nem",
      },
      cancel: {
        text: "Mégse",
      },
      apply: {
        text: "Alkalmaz",
      },
      save: {
        text: "Mentés",
      },
      submit: {
        text: "Beküld",
      },
      help: {
        text: "Segítség",
      },
      close: {
        text: "Bezár",
      },
    },
    closeToolText: "Panel bezárása",
  },
});

Ext.define("Ext.locale.hu.picker.Date", {
  override: "Ext.picker.Date",

  config: {
    doneButton: "Kész",
    monthText: "Hónap",
    dayText: "Nap",
    yearText: "Év",
  },
});

Ext.define("Ext.locale.hu.picker.Picker", {
  override: "Ext.picker.Picker",

  config: {
    doneButton: "Elkészült",
    cancelButton: "Mégse",
  },
});

Ext.define("Ext.locale.hu.panel.Date", {
  override: "Ext.panel.Date",

  config: {
    nextText: "Következő hónap (Ctrl + Jobbra)",
    prevText: "Előző hónap (Ctrl + Balra)",
    buttons: {
      footerTodayButton: {
        text: "Ma",
      },
    },
  },
});

Ext.define("Ext.locale.hu.panel.Collapser", {
  override: "Ext.panel.Collapser",

  config: {
    collapseToolText: "Panel összecsukása",
    expandToolText: "Panel kibontása",
  },
});

Ext.define("Ext.locale.hu.field.Field", {
  override: "Ext.field.Field",

  config: {
    requiredMessage: "Ez a mező nem lehet üres",
    validationMessage: "Hibás formátum",
  },
});

Ext.define("Ext.locale.hu.field.Number", {
  override: "Ext.field.Number",

  decimalsText: "A tizedesjegyek maximális száma {0}",
  minValueText: "A mező minimális értéke {0}",
  maxValueText: "A mező maximális értéke {0}",
  badFormatMessage: "Az érték nem szám",
});

Ext.define("Ext.locale.hu.field.Text", {
  override: "Ext.field.Text",

  badFormatMessage: "Az érték nem felel meg a szükséges formátumnak",
  config: {
    requiredMessage: "Ez a mező nem lehet üres",
    validationMessage: "Hibás formátum",
  },
});

Ext.define("Ext.locale.hu.Dialog", {
  override: "Ext.Dialog",

  config: {
    maximizeTool: {
      tooltip: "Teljes képernyőre maximalizálás",
    },
    restoreTool: {
      tooltip: "Eredeti méretre visszaállítás",
    },
  },
});

Ext.define("Ext.locale.hu.field.FileButton", {
  override: "Ext.field.FileButton",

  config: {
    text: "Tallózás...",
  },
});

Ext.define("Ext.locale.hu.dataview.List", {
  override: "Ext.dataview.List",

  config: {
    loadingText: "Adatok betöltése...",
  },
});

Ext.define("Ext.locale.hu.dataview.EmptyText", {
  override: "Ext.dataview.EmptyText",

  config: {
    html: "Nincs megjeleníthető adat",
  },
});

Ext.define("Ext.locale.hu.dataview.Abstract", {
  override: "Ext.dataview.Abstract",

  config: {
    loadingText: "Adatok betöltése...",
  },
});

Ext.define("Ext.locale.hu.LoadMask", {
  override: "Ext.LoadMask",

  config: {
    message: "Adatok betöltése...",
  },
});

Ext.define("Ext.locale.hu.dataview.plugin.ListPaging", {
  override: "Ext.dataview.plugin.ListPaging",

  config: {
    loadMoreText: "Több betöltése...",
    noMoreRecordsText: "Nincsenek további bejegyzések",
  },
});

Ext.define("Ext.locale.hu.dataview.DataView", {
  override: "Ext.dataview.DataView",

  config: {
    emptyText: "",
  },
});

Ext.define("Ext.locale.hu.field.Date", {
  override: "Ext.field.Date",

  minDateMessage: "A dátumnak ezen a dátumon kell lennie: {0}",
  maxDateMessage: "A dátumnak ezen a dátumon kell lennie: {0}",
});

Ext.define("Ext.locale.hu.grid.menu.SortAsc", {
  override: "Ext.grid.menu.SortAsc",

  config: {
    text: "Növekvő sorrend",
  },
});

Ext.define("Ext.locale.hu.grid.menu.SortDesc", {
  override: "Ext.grid.menu.SortDesc",

  config: {
    text: "Csökkenő sorrend",
  },
});

Ext.define("Ext.locale.hu.grid.menu.GroupByThis", {
  override: "Ext.grid.menu.GroupByThis",

  config: {
    text: "Csoportosítás e szerint a mező szerint",
  },
});
Ext.define("Ext.locale.hu.grid.menu.ShowInGroups", {
  override: "Ext.grid.menu.ShowInGroups",

  config: {
    text: "Csoportokban megjelenítés",
  },
});

Ext.define("Ext.locale.hu.grid.menu.Columns", {
  override: "Ext.grid.menu.Columns",

  config: {
    text: "Oszlopok",
  },
});

Ext.define("Ext.locale.hu.data.validator.Presence", {
  override: "Ext.data.validator.Presence",

  config: {
    message: "Jelen kell lennie",
  },
});

Ext.define("Ext.locale.hu.data.validator.Format", {
  override: "Ext.data.validator.Format",

  config: {
    message: "Helytelen formátum",
  },
});

Ext.define("Ext.locale.hu.data.validator.Email", {
  override: "Ext.data.validator.Email",

  config: {
    message: "Érvénytelen e-mail cím",
  },
});

Ext.define("Ext.locale.hu.data.validator.Phone", {
  override: "Ext.data.validator.Phone",

  config: {
    message: "Érvénytelen telefonszám",
  },
});

Ext.define("Ext.locale.hu.data.validator.Number", {
  override: "Ext.data.validator.Number",

  config: {
    message: "Nem szám",
  },
});

Ext.define("Ext.locale.hu.data.validator.Url", {
  override: "Ext.data.validator.Url",

  config: {
    message: "Érvénytelen URL",
  },
});

Ext.define("Ext.locale.hu.data.validator.Range", {
  override: "Ext.data.validator.Range",

  config: {
    nanMessage: "Numerikusnak kell lennie",
    minOnlyMessage: "A mező minimális értéke {0}",
    maxOnlyMessage: "A mező maximális értéke {0}",
    bothMessage: "{0} és {1} között kell lennie",
  },
});

Ext.define("Ext.locale.hu.data.validator.Bound", {
  override: "Ext.data.validator.Bound",

  config: {
    emptyMessage: "Jelen kell lennie",
    minOnlyMessage: "Az értéknek nagyobbnak kell lennie, mint {0}",
    maxOnlyMessage: "Az értéknek kisebbnek kell lennie, mint {0}",
    bothMessage: "Az értéknek {0} és {1} között kell lennie",
  },
});

Ext.define("Ext.locale.hu.data.validator.CIDRv4", {
  override: "Ext.data.validator.CIDRv4",

  config: {
    message: "Nem érvényes CIDR blokk",
  },
});

Ext.define("Ext.locale.hu.data.validator.CIDRv6", {
  override: "Ext.data.validator.CIDRv6",

  config: {
    message: "Nem érvényes CIDR blokk",
  },
});

Ext.define("Ext.locale.hu.data.validator.Currency", {
  override: "Ext.data.validator.Currency",

  config: {
    message: "Nem érvényes pénznem összeg",
  },
});

Ext.define("Ext.locale.hu.data.validator.DateTime", {
  override: "Ext.data.validator.DateTime",

  config: {
    message: "Nem érvényes dátum és idő",
  },
});

Ext.define("Ext.locale.hu.data.validator.Exclusion", {
  override: "Ext.data.validator.Exclusion",

  config: {
    message: "Kizárt érték",
  },
});

Ext.define("Ext.locale.hu.data.validator.IPAddress", {
  override: "Ext.data.validator.IPAddress",

  config: {
    message: "Nem érvényes IP-cím",
  },
});

Ext.define("Ext.locale.hu.data.validator.Inclusion", {
  override: "Ext.data.validator.Inclusion",

  config: {
    message: "Nem szerepel az engedélyezett értékek listáján",
  },
});

Ext.define("Ext.locale.hu.data.validator.Time", {
  override: "Ext.data.validator.Time",

  config: {
    message: "Nem érvényes idő",
  },
});

Ext.define("Ext.locale.hu.data.validator.Date", {
  override: "Ext.data.validator.Date",

  config: {
    message: "Nem érvényes dátum",
  },
});

Ext.define("Ext.locale.hu.data.validator.Length", {
  override: "Ext.data.validator.Length",

  config: {
    minOnlyMessage: "A hosszúságnak legalább {0} kell lennie",
    maxOnlyMessage: "A hosszúság nem lehet több, mint {0}",
    bothMessage: "A hosszúságnak {0} és {1} között kell lennie",
  },
});

Ext.define("Ext.locale.hu.ux.colorpick.Selector", {
  override: "Ext.ux.colorpick.Selector",

  okButtonText: "OK",
  cancelButtonText: "Mégse",
});

// Ez szükséges, amíg minden lokalizációt külön fájlokba nem tudunk refaktorálni
Ext.define("Ext.locale.hu.Component", {
  override: "Ext.Component",
});

Ext.define("Ext.locale.hu.grid.filters.menu.Base", {
  override: "Ext.grid.filters.menu.Base",

  config: {
    text: "Szűrő",
  },
});

Ext.define("Ext.locale.hu.grid.locked.Grid", {
  override: "Ext.grid.locked.Grid",

  config: {
    columnMenu: {
      items: {
        region: {
          text: "Régió",
        },
      },
    },
    regions: {
      left: {
        menuLabel: "Zárolt (Bal)",
      },
      center: {
        menuLabel: "Feloldott",
      },
      right: {
        menuLabel: "Zárolt (Jobb)",
      },
    },
  },
});

Ext.define("Ext.locale.hu.grid.plugin.RowDragDrop", {
  override: "Ext.grid.plugin.RowDragDrop",
  dragText: "{0} sor kiválasztva",
});
