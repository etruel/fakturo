# Fakturo smoke test

One command that walks the main flow on a test site, checks the results and deletes everything it made.

```
cd /path/to/wp
wp eval-file wp-content/plugins/fakturo/tests/smoke.php            # add --url=... on a multisite
FKTR_SMOKE_SKIP_SCREENS=1 wp eval-file ...                         # only the in-process checks
```

Exit code 0 when everything passed, 1 otherwise. Run it with `WP_DEBUG` and `WP_DEBUG_LOG` on: the last check fails if PHP wrote anything to `debug.log`.

It writes data (and removes it), so it refuses to run unless the site is `local`/`development`/`staging` (WP_ENVIRONMENT_TYPE), has a `.local`/`.test`/`localhost` address, or `FKTR_SMOKE=1` is set. Fakturo must be active and set up (default currency, an invoice type that takes stock out and one that does not, a sale point, a stock location, stock control on). The numbering options are put back at the end.

## What it checks

| Area | Checks |
| --- | --- |
| Numbers, dates, money | the four separator combinations, rounding, typed dates, invalid dates, time zones (Buenos Aires, Madrid, Kolkata), every report range, text with quotes and backslashes |
| Client, product | create/save, balance, reading an id that does not exist, stock in and out |
| Atomic helpers | consecutive reserved invoice numbers, balance without floating point dust, locks that can be asked for again |
| Invoice | publish, exact client name, stock taken, last number recorded, duplicate number stays a draft with a notice, not enough stock stays a draft and takes nothing, PDF engine |
| Receipt | publish, balance, payment listed on the invoice, saving twice does not repeat the effects, cancelling gives back once, duplicate number gets the next free one |
| Subscriptions add-on | end of month does not bill again the same day, month/year clamping, cron string in site time, task scheduled |
| Screens over HTTP | every admin screen of Fakturo (lists, new, edit, taxonomies, settings, reports) loads without a PHP notice, 11 AJAX calls, the AJAX nonce is required, print views and PDFs of an invoice and a receipt, print links need their token |
| Log | `debug.log` did not grow |

## What it does not cover (check by hand before a release)

* The setup wizard end to end, and a fresh install / activation.
* Licenses and updates against the store; auto-update switches.
* Sending e-mail (it would send real mail), the e-mail templates preview.
* Pro Sites: a site that gets Fakturo by plan, network screens, the network billing cron called from outside.
* How the screens look (JS widgets, select2, date pickers): open the invoice, receipt, product and client screens in the browser and watch the console.
* `fakturo_afip` and other add-ons.

## Adding a check

Open `smoke.php`, pick the section and call `fktrSmoke::ok($condition, 'what must be true', 'detail shown on failure')`. Anything that creates a post goes through `fktrSmoke::post()` so it is deleted at the end; `fktrSmoke::submit()` saves a post the way its edit screen does (form fields plus the save nonce).

## Handler audit

`python3 tests/audit-handlers.py includes ../fakturo_subscriptions/includes` lists the AJAX and admin-post handlers whose body shows no permission check or no nonce (it counts `fktr_guard` as both), redirects to a request value, a nonce read without `isset`, and `update_option()` from `$_POST`. Handlers checked by hand that the patterns cannot see are listed in `REVIEWED` with the reason. Run it after adding a handler; at the moment core and Subscriptions have nothing to review and `fakturo_afip` still has (see the TODO).
