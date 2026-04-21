Ext.define('JBXAdmin.util.Slugify', {
    singleton: true,
    alternateClassName: 'Slugify',

    /**
     * Converts an arbitrary string to a URL-safe slug.
     * Maps Hungarian + common Latin-1 diacritics to ASCII, lowercases,
     * collapses non-[a-z0-9] runs into single hyphens, and trims ends.
     *
     * @param {String} input
     * @return {String}
     */
    toSlug: function (input) {
        if (input === null || input === undefined) return '';
        var map = {
            'á':'a','é':'e','í':'i','ó':'o','ö':'o','ő':'o','ú':'u','ü':'u','ű':'u',
            'Á':'a','É':'e','Í':'i','Ó':'o','Ö':'o','Ő':'o','Ú':'u','Ü':'u','Ű':'u',
            'ä':'a','ë':'e','ï':'i','ß':'ss','ñ':'n','ç':'c',
            'Ä':'a','Ë':'e','Ï':'i','Ñ':'n','Ç':'c'
        };
        return String(input)
            .replace(/[áéíóöőúüűÁÉÍÓÖŐÚÜŰäëïßñçÄËÏÑÇ]/g, function (c) { return map[c] || c; })
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
