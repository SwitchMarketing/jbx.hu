/**
 * Fotózás közben csak olvasó kérések mehetnek át, plusz a belépés.
 * Minden más (mentés, törlés, feltöltés, állapotváltás) blokkolva — így a
 * screenshot-szkript garantáltan nem módosíthat adatot.
 */
export function isAllowedRequest(method, url) {
    const m = String(method).toUpperCase();

    if (m === 'GET' || m === 'HEAD' || m === 'OPTIONS') {
        return true;
    }

    const path = new URL(url).pathname.replace(/\/+$/, '');

    return m === 'POST' && path.endsWith('/admin/login');
}
