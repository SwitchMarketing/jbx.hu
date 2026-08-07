# jbx.hu

A JBX Trade Kft. publikus weboldala és webshopja — a svéd Axelent brand (X-Guard gépvédő kerítések, X-Tray kábeltálcák, X-Protect ütközésvédelem, X-Store raktározási megoldások) magyarországi képviselete.

## Tech stack

- **Backend:** [CodeIgniter 4](https://codeigniter.com/user_guide/) (PHP 7.4 / 8.0+)
- **Adatbázis:** MySQL
- **Frontend (publikus oldal):** SCSS + vanilla JS, [Gulp](https://gulpjs.com/) build
- **Admin panel:** [Sencha ExtJS 7](https://www.sencha.com/products/extjs/) SPA
- **Deploy:** GitHub Actions → SSH (lásd [Deploy](#deploy) lentebb)

## Előfeltételek

- PHP 7.4+ vagy 8.0+, a szükséges kiterjesztésekkel: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`
- [Composer](https://getcomposer.org/)
- Node.js + npm
- MySQL szerver (helyi vagy elérhető dev DB)
- A `public/admin` Sencha build-eléséhez [Sencha Cmd](https://www.sencha.com/products/extjs/cmd-download/) (csak akkor kell, ha az admin panelen dolgozol)

## Telepítés

```bash
composer install
npm install
```

Hozz létre egy `.env` fájlt a projekt gyökerében (ez a repóban **nincs benne**, git-ignorolt — kérj egy működő verziót egy csapattagtól, vagy állítsd össze a [CodeIgniter env dokumentáció](https://codeigniter.com/user_guide/general/environments.html) alapján). Amit mindenképp be kell állítani:

- `CI_ENVIRONMENT` (`development` helyi géphez)
- `app.baseURL`
- `database.default.*` — adatbázis kapcsolat (host, név, felhasználó, jelszó)
- Google / Facebook / GTM kulcsok, ha ezekhez tartozó funkciókon dolgozol (lásd `App\Libraries\BuildPage`)

Adatbázis séma előkészítése:

```bash
php spark migrate
```

## Helyi fejlesztés

```bash
php spark serve       # CI4 beépített dev szerver
gulp                   # SCSS + JS figyelése, browser-sync proxy http://dev.jbx.hu-ra
```

Admin panel fejlesztéshez (`public/admin` mappában):

```bash
cd public/admin
sencha app watch
```

**Fontos:** admin panel változtatás commitolása előtt mindig futtasd le a production buildet is — a build output (`public/admin/build/production/JBXAdmin/`) van verziókezelve és az kerül élesbe:

```bash
cd public/admin
sencha app build production
```

## Adatbázis

Két logikai kapcsolat van definiálva (`default` és `shop`, lásd `app/Config/Database.php`), production környezetben ugyanarra a MySQL adatbázisra mutatnak.

A termékadatok normalizált modellben élnek (`product_masters` / `product_variants` / `attributes` + `variant_attribute_values`) — új shop funkciónál ezeket használd a régi, lapos `products` tábla helyett (az csak UNAS import/backfill célra maradt meg).

Hasznos spark parancsok:

```bash
php spark migrate                 # migrációk lefuttatása
php spark db:normalize-products   # product_masters/variants/attributes újraépítése a flat products táblából
php spark unas:import-all         # legacy UNAS adatok importja (app/Commands/ImportUnas*.php)
```

Ha kategóriát vagy blog posztot módosítasz az admin panelen, a route cache-eket is regenerálni kell (`writable/cache/category_routes.php`, `writable/cache/blog_routes.php`) — enélkül az új slug-ok 404-et adnak.

## Tesztek

```bash
composer test                             # phpunit, teljes suite
vendor/bin/phpunit --filter TestName      # egy adott teszt
```

## Deploy

Production deploy **automatikus**: minden `master`-re push GitHub Actionst indít ([.github/workflows/deploy-production.yml](.github/workflows/deploy-production.yml)), ami SSH-n keresztül (`tar` streamelve) felmásolja az `app/`, `public/` (admin nélkül) és a már buildelt Sencha admin bundle tartalmát a szerverre, majd egy smoke check-kel ellenőrzi hogy az oldal és az admin válaszol-e.

A CI **nem** futtat build/minify lépéseket — a gulp-os SCSS/JS compile, a `php spark minify:all`, és a Sencha build mind lokális, commit előtti felelősségek. Ha valamelyiket elfelejted lokálisan lefuttatni és commitolni, az a deployban sem fog megjelenni.

Rollback egy rossz deploy után:
- **Módosított fájlok:** `git revert <sha> && git push` — a következő automatikus deploy visszaállítja az előző tartalmat.
- **Hozzáadott fájlok:** a deploy soha nem töröl a szerveren semmit, így egy rossz commit által hozzáadott fájlt a revert nem távolít el — ezeket kézzel kell törölni SSH-n keresztül.
- **Vészhelyzet:** ha maga a GitHub Actions pipeline hibás, a `cpanel-legacy` git remote és a `.cpanel.yml` továbbra is működik tartalék manuális deploy útvonalként.

Kézi/lokális deploy (nem szükséges a mindennapi munkához, de elérhető):

```bash
npm run deploy         # production: minify + public + app (FTP)
npm run deployadmin    # Sencha rebuild + upload
npm run deploystaging  # staging variáns
```

## Projekt felépítés

- `app/Controllers/*.php` — vékony kontrollerek, `App\Libraries\BuildPage::render()`-en keresztül renderelnek
- `app/Config/Routes.php` — statikus route-ok + dinamikusan generált kategória/blog route-ok (`writable/cache/*.php`)
- `app/Models/` — `ProductMasterModel`, `ProductVariantModel`, `AttributeModel` (normalizált termékmodell); `ProductModel` csak legacy/import célra
- `app/Controllers/Admin/*.php` — admin JSON API, mind `BaseResourceController`-ből származik
- `public/admin/` — Sencha ExtJS admin SPA forrás + production build
- `app/Libraries/Unas.php`, `app/Helpers/UnasImport.php`, `app/Commands/ImportUnas*.php` — legacy UNAS webshop platform import

## Konvenciók

- Az útvonalak, nézet-szövegek és a legtöbb felhasználói szöveg **magyar** nyelvű.
- Commit üzenetek magyarul íródnak.
