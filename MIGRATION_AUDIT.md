# AAAS Laravel conversion: database and deployment audit

Date: 17 November 2026  
Scope: local conversion of `www.aaasindia.org`. The existing
`conference.aaasindia.org` Laravel project remains in place and continues to
use the same database.

## Important conclusion

Do **not** delete tables from the current shared database. The conference
application actively uses a mixture of Laravel tables and legacy `oc_` tables.
Deleting seemingly e-commerce-related data before both sites have been moved
and tested could break a conference registration, abstract, award document, or
payment record.

The safe way to obtain a lean database is:

1. Keep the present database unchanged as the rollback source.
2. Finish the local Laravel parity checks for both sites.
3. Create a fresh production database from explicit Laravel migrations and a
   tested data-import command.
4. Copy only the required tables/data into that new database.
5. Point both Laravel applications at it only in the planned cutover window.
6. Retain the old database as a read-only backup until the agreed retention
   period has passed.

## Tables that must be retained

### Main-site administration and public content

- `oc_user` — main administrator login.
- `oc_customer`, `oc_customer_type`, `oc_country`, `oc_zone` — member data and
  member form dropdowns.
- `oc_information`, `oc_information_description`, `oc_seo_url` — CMS pages and
  their existing URLs.
- `oc_slider`, `oc_news`, `oc_downloads`, `oc_newsletter_editors` — homepage
  content.
- `oc_books`, `oc_report`, `oc_abstract`, `oc_awards`, `oc_videos`,
  `oc_gallery`, `oc_gallery_images`, `oc_feedback` — public content and admin
  modules.

### Conference website (confirmed from its existing Laravel source)

- `users`, `password_resets`, `failed_jobs` — conference authentication and
  Laravel operational data.
- `oc_conference`, `oc_con_payment`, `oc_con_session` — conference pages,
  pricing and abstract sessions.
- `oc_organisers`, `oc_local`, `oc_national`, `oc_international`,
  `oc_keynotes`, `oc_plenery` — conference speakers/organisers.
- `absubmissions`, `payments`, `oc_conf_passport`, `oc_conf_accomodation` —
  registrations, abstracts, payment proof, passport and accommodation data.
- `oc_award_nominees`, `oc_awards`, `oc_customer` — award file downloads
  exposed by the existing conference application.

## Tables requiring functional-parity confirmation

These must remain until the account, election, and award workflows have been
compared against live and implemented locally:

- Tables matching `oc_election%`, `oc_main_%`, `oc_final_%`, `oc_nominee%`,
  and `oc_award%`.
- Any table used by live member profile, password-reset, nomination, voting, or
  award-upload actions.
- Uploaded files under the live `image/` and conference storage directories.

## Candidates for later archival (not deletion yet)

After both Laravel apps are running against the new database and a backup has
been validated, the following OpenCart-only families can be excluded from the
new database **only if no live route or custom extension references them**:

- Product/catalogue: `oc_product%`, `oc_category%`, `oc_option%`,
  `oc_manufacturer%`, `oc_filter%`, `oc_attribute%`, `oc_review`.
- Store checkout: `oc_cart`, `oc_order%`, `oc_return%`, `oc_coupon%`,
  `oc_voucher%`, `oc_recurring%`, `oc_tax%`, `oc_stock_status`.
- Store platform/configuration: `oc_extension%`, `oc_theme%`, `oc_marketplace%`,
  `oc_api%`, `oc_setting`, `oc_layout%`, `oc_event`, `oc_session`.

This is an exclusion guide for a new database build—not permission to run a
destructive SQL command against the present database.

## Local completion checklist

- [x] CMS content uses a visual rich-text editor and saves HTML.
- [x] CMS and generic listing previews show readable text rather than escaped
  HTML.
- [x] Main conference admin has conference edit, registrations, abstracts and
  download actions backed by the shared tables.
- [x] Public main-site books, reports, abstracts, gallery, videos and search
  routes are available locally.
- [ ] Compare live and local account/profile/password workflow field by field.
- [ ] Compare live election and award workflow field by field.
- [ ] Run URL-by-URL regression testing, including header/footer links and
  legacy query-string URLs.
- [ ] Verify uploads and downloaded documents using real test copies.
- [ ] Take and restore-test a production database and uploads backup before
  deployment.

## Low-cost AWS design (for approval after local testing)

For the current low traffic, use one small EC2 instance in Mumbai with both
Laravel applications, Nginx/PHP-FPM and MySQL on the same server. Keep an EBS
volume, an Elastic IP, daily database backup, and Let's Encrypt SSL. This is
cheaper than adding RDS, a load balancer, or separate servers, which are not
justified at this traffic level.

During cutover, point `aaasindia.org` / `www` and `conference` GoDaddy DNS
records to the Elastic IP, configure two Nginx virtual hosts, and use one
database connection for both applications. The exact instance size should be
selected after observing memory usage locally/staging; the present t2.micro has
only 1 GB RAM, so a 2 GB general-purpose instance is the safer starting point
for PHP plus MySQL.
