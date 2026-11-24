# Test clone: `/var/www/landas-test`

Card 6b03c8be082e813a8c1ffe6f (A3). Until 2026-11-24 there was **no** test clone on this
host, and `tests/TestCase.php` refuses to run from `/var/www/landas-fixit` (the production
checkout) by design — see commit `c7583595`. So the suite could not legally run anywhere.
This is the clone it names.

## Run the suite from now on

```bash
cd /var/www/landas-test
sudo -u www-data env LOG_CHANNEL=stderr php vendor/bin/phpunit --no-coverage
```

A single file or directory works the same way:

```bash
cd /var/www/landas-test
sudo -u www-data env LOG_CHANNEL=stderr php vendor/bin/phpunit --no-coverage tests/Feature/Tenant
```

`LOG_CHANNEL=stderr` is optional. It is what you want when debugging an exception, because the
app logs the SQL/stack context there; without it `phpunit.xml` sets `LOG_CHANNEL=null` and
nothing is logged.

**Do not run it as root.** Root's umask (0022) makes `storage/` and `bootstrap/cache` files
unwritable for www-data and the next www-data run then fails on files it cannot rewrite. If you
already did, `chown -R www-data:www-data storage bootstrap/cache`.

## How it was built

```bash
git clone --branch master --single-branch https://github.com/cyddalupan/agency.git /var/www/landas-test
cd /var/www/landas-test
composer install --no-interaction --prefer-dist      # ~102 MB vendor
```

Then, in the clone only:

1. `.env` (never tracked; `.gitignore` has it):
   - `APP_KEY` — `php artisan key:generate --force`. **`phpunit.xml` sets no key and
     `.env.testing` is gitignored**, so the key must live in `.env`.
   - `DB_CONNECTION=sqlite` — the clone must never be able to reach MySQL. `phpunit.xml` pins
     `sqlite`/`:memory:` for the suite anyway, but a stray `php artisan` reads this file.
   - `APP_UNIVERSE=2` — production runs universe 2 ("LANDAS"). Without it the app falls back to
     `config('app.universe', 1)` = "Agency Super", and any test asserting a brand name fails for
     a reason that has nothing to do with the code under test.
   - `LOG_CHANNEL=stderr`.
   - runtime-editable files (`public/build`) — below.
2. `chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache`
3. `.env` must be **www-data-readable** (`chown www-data:www-data .env; chmod 640`). A `600`
   root-owned file is silently skipped by the dotenv loader, and the run dies with
   `MissingAppKeyException` rather than a permissions error.

### `public/build` is a build artifact, not source

`resources/views/layouts/app.blade.php` calls `@vite(...)`, so every rendered page needs
`public/build/manifest.json`. The suite does not build assets, so a fresh clone 500s on any page
test with `Vite manifest not found`. Copy the production artifact once:

```bash
cd /var/www/landas-fixit && tar -cf - public/build | (cd /var/www/landas-test && tar -xf -)
chown -R www-data:www-data /var/www/landas-test/public/build
```

It is 424 KB and only needed for page-rendering tests. `npm install && npm run build` also works
and produces a byte-different manifest; copying is cheaper and matches what production renders.

## What the suite actually touches

`phpunit.xml` pins `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` for every test, so **no
database is created, migrated or dropped on this host by a test run**. The MySQL grant question
does not apply: there is no MySQL connection at test time. Mutating runs against the scratch
`landas_test` database belong to feature rehearsals, not to this clone.

`FILESYSTEM_DISK` is pinned to the `testing` disk (`/tmp/landas-fixit-tests/storage`), and
`SESSION_DRIVER`/`CACHE_STORE` to `array`, so a run writes nothing into the clone's
`storage/app` or `storage/logs`.

## Cost

- clone + vendor: ~118 MB (`.git` 3.6 MB, `vendor` 102 MB)
- `composer install --no-dev` instead: `vendor` drops to 63 MB, but PHPUnit is a dev
  dependency, so a `--no-dev` vendor cannot run the suite. It is only useful for a clone that
  serves pages.
- no MySQL database, no node_modules required.

## Recreating / refreshing the clone

```bash
sudo -u www-data git -C /var/www/landas-test pull --ff-only
# if composer.lock changed:
cd /var/www/landas-test && composer install --no-interaction --prefer-dist
# if public/build is stale after a frontend change:
#   re-copy it as above
```

If you ever need a clean tree rather than a pull, delete `/var/www/landas-test` and repeat the
build steps — do not copy `/var/www/landas-fixit`, which carries a production `.env`, 4,858 live
files under `storage/app`, and the production log.

## Escape hatch, and why not to use it

`ALLOW_PROD_TEST_RUN=1` makes `TestCase` run from the production checkout. It exists for a
deliberate read-only probe only. Every run started that way boots with production `.env`; the
only thing between it and live data is what the individual test happens to touch.
