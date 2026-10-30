=== Fakturo ===
Contributors: etruel,sniuk,khaztiel,axellourenco
Donate link:
Tags: invoice,sales,inventory,billing,receipt
Requires at least: 4.9
Tested up to: 7.2
Requires PHP: 7.4
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The best and more complete open source Invoice System. An easy and fully-configurable SME Management system for Wordpress.

== Description ==

Fakturo is a complete system SME management, released as a Wordpress plugin with multiple functions for small and medium-sized companies.
It's FREE, fully-configurable, customizable and upgradable through Wordpress filters and also by AddOns or customized support.

NOTE: This plugin is in Beta stage. The 1.0 version will be the official release but you can start to use it now ;-)
The release date is close, we are working on it every day!!
We really enjoy and thank if you can check and test the Fakturo.
We are receiving feedbacks in english and spanish at the Wordpress support forums and in the [development repository](https://github.com/etruel/fakturo).

= Introduction =
There are many systems to manage invoices on the web. Some freeware, some modular, some online, some with addons, some with e-invoicing, some with stock, some easy, some are updated periodically, some of them with technical support, etc... always "some"..., so that's why was born Fakturo, because it is not "some" more, but that it has all the above and much more.

= Features =
* Manage customers, suppliers, contacts and branches.
* Manage and load stock for individual products per deposits.
* Invoicing of suppliers and customers with automatic input and output of stock.
* Send invoices or accounts of customers by email in automatic or manual way.
* Manage payments, partial or total bills for customers and suppliers.
* Input and output of money box.
* Daily box and Balance.
* Reports.
* System of templates to customize invoices and all what is printed.
* Built-in Backup and restore system.
* Custom configuration of types of products, Packaging, deposits and scales of prices, origins.
* Coins, entities Bank, countries and provinces or States.
* Taxes, percentages.
* Custom user roles for salesman and manager permissions.
* Developer friendly with dozens of actions and filters.
* Optimized the use of WP tables without losing the standard for large amounts of bills and stock movements.

== Installation ==

You can either install it automatically from the WordPress admin, or do it manually:

= Using the Plugin Manager =

1. Click Plugins
2. Click Add New
3. Search for `fakturo`
4. Click Install
5. Click Install Now
6. Click Activate Plugin
7. Now you must see the Fakturo Items on the Wordpress menu

= Manually =

1. Upload `fakturo` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Settings Screens.

1. Adding a Client.

1. Adding an Invoice.

1. Adding a Receipt.

1. Adding a Product.


== Frequently Asked Questions ==

= I have this plugin installed and activated. What must I do now ? =

* Go To Fakturo settings and set up the plugin configurations. Is easy, but there are many options that must be setup following your country's standards.


== Changelog ==

= 1.1 =
* Welcome and Getting Started screens now speak English out of the box with full Spanish translation, and link to the right Fakturo pages.
* Ready for PHP 8.2 and 8.3: no more deprecated notices when reading settings, printing or exporting PDFs.
* PDFs of invoices, receipts and reports keep every character, including accents, symbols and non-Latin alphabets.
* Faster and more robust invoice and receipt number validation, product search and report queries.
* Webcam snapshots work again in Chrome: they use the browser camera instead of the long-gone Flash. The plugin is also lighter, without the unused webcam demos, Flash file and sample dashboard widgets.
* Webcam library updated to WebcamJS 1.0.26 (adds iOS Safari support); the unminified script loads when SCRIPT_DEBUG is on.
* PDF engine updated to Dompdf 3.1.6 (with the security fixes of the 2.x series), and PHP 7.4 to 8.3 compatible. Invoices, receipts, print templates, emails and reports keep rendering exactly the same.
* Settings, system and Licenses screens no longer show a PHP notice in the page header on PHP 8.1 and later.
* Print and e-mail templates: every object type now comes with a ready-to-use default, created by the setup wizard or with the new "Create default templates" button.
* Choosing the object of a new template fills in its default content and subject, and "Reset to default" no longer leaves a template empty.
* A warning shows when several published templates are assigned to the same object; the newest one is always the one used.
* Template titles and totals are now always available to print and e-mail templates, and the editor fields are escaped.
* The default invoice template no longer overlaps the product lines when an invoice has several items.
* The stock report no longer shows PHP notices when it is opened without filters.
* Requires PHP 7.4 or later (tested up to PHP 8.3) and tested with WordPress 7.2.
* Multisite: license keys of the add-ons are managed once for the whole network by the super admin, in the new Fakturo menu of the network admin, and the sites no longer show the Licenses screen.
* Multisite: the Fakturo menu of the network admin also holds the global settings: default values for new sites (date format, separators, decimals, digits of numbers and stock) and an option to keep Fakturo out of sight on the main site. The add-ons list is downloaded once for the network.
* Dates in invoices, receipts and subscriptions follow the date format chosen in Settings (no more "2727/1010/2026" on installs that never saved it).
* Security: Fakturo sellers can no longer open the Settings or Licenses screens, and saving a client, provider, product, invoice or receipt now checks the permission of that very item.
* Security: Fakturo managers can only edit, remove or delete users who are managers or sellers, never the site administrator, and can no longer edit files. Managers can now save the Settings screens.
* Deactivating the plugin takes its permissions away from administrators again, and role changes reach existing sites without waiting for a new version.
* Security: every AJAX call and link of the admin (print, e-mail, cancel, preview, copy and reset of invoices, receipts and templates) now checks the permission of the user, the item it acts on and a security token that belongs to that item. Cancelling a receipt twice no longer gives the balance back twice.
* Scripts and styles of the plugin are reloaded by the browser as soon as they change, so an update never leaves old files in the cache.
* Security: the edit screens of clients, providers, products, invoices, receipts and templates only save with their own security token and keep only the fields that belong to each type, instead of storing the whole form. Updating one of these items from code or an import no longer wipes its data, and old copies of the title, status and form leftovers are removed from the saved data.
* Security: the three Settings screens validate every value they save (numbers, separators, lists, links and texts), drop unknown fields, and a new site, or an upgrade that adds an option, always gets every setting with its default instead of PHP notices. Opening a Settings screen no longer rewrites the saved options.
* Security: the setup wizard only works for users who can manage it: its steps and the three loaders of countries, states and currencies check the permission together with the token, accept only valid ids, and no longer show PHP notices when a step arrives incomplete or with apostrophes in the data.
* Receipts now record their own number on the invoices they pay, and a cancelled or deleted receipt disappears from the payment status of its invoices, which show Unpaid again.
* Security: client, provider, product, invoice and settings data is escaped when it is shown in the edit screens, lists, reports, PDFs and e-mails, so text with quotes or HTML can no longer break a form or run code in another user's session.
* Invoices keep the client name exactly as typed when it contains quotes.
* A receipt number with letters no longer breaks the new receipt screen.
* Print and e-mail templates can only call an approved list of functions; anything else is rejected with a message when the template is saved, printed or sent, and the include tag is disabled.
* PDFs only load images and styles from the site's own address.
* In a network, a new site is ready the moment Fakturo is switched on in it: roles and permissions exist before anybody signs in and its administrator lands on the setup wizard (the network can turn that off in Global settings).
* Deleting Fakturo from the Plugins screen now removes its roles, permissions and temporary data; the new setting *Delete data on uninstall* (in Fakturo settings, and in the network settings for the license keys) also removes the invoices, receipts, clients, products, templates, lists and settings. It is off by default, so a plugin deleted by mistake does not take the documents with it.
* With a persistent object cache (Redis, Memcached), editing a tax, currency, sale point or any other list item shows the new values at once instead of the old ones until the cache expires.

= 1.0.10 (Oct 23, 2026) =
* New Add-ons screen: activate, license and manage auto-updates of each add-on from its own row, with bulk actions and live search.
* Free add-ons from WordPress.org, like Fakturo Stock in List, can be installed right from the Add-ons screen.
* Faster Licenses screen: the store answer is remembered and the status of every add-on license is shown at a glance.
* Activate, deactivate and check licenses without reloading the page.
* Add-on updates are also detected by scheduled WP-Cron checks.
* Secure connection with the license store, with updated EDD updater 1.9.4.
* Setup wizard loads its texts at the right moment and prints clean styles on its standalone page.
* Updated Spanish translation.

= 1.0.9 (Apr 22, 2025) =
* Improved and fixed the seller commission calculations on sales.
* Added restriction rules for applying taxes when creating a product.
* Added seller commission calculation by sales quantity.
* Added "Print Receipt" button inside the Receipt Edit screen.
* Improved the PDF layout in the Summary Report.
* Improved the Client Account Report with updated filters.
* Added template variables for generating print templates of receipts.
* Updated the Income Report with better calculations.
* Improves styles and functionality of the PDF and CSV export buttons.

= 1.0.8 (Oct 17, 2024) =
* Added features to export data as PDF or CSV in all reports.
* Added Responsive views in all reports to allow see them also on cellphones. 
* Fixes filters with custom dates range in all reports.
* Fixes many styles and margins in all reports.
* Fixes some tax variables in templates.
* Improves load TextDomain for languages files.

= 1.0.7 (Sep 27, 2024) =
* Added new report by incomes to show a summary or details of receipts by clients. 
* Fixes Summary report to show a new column with the client. 
* Fixes graphics report on responsive screens.
* Fixes reports on quarter or custom dates filters formats.
* Fixes Tax in invoice template to show the porcent instead of money with var {$invoice.uc_tax_porcent.0}
* Fixes few styles.

= 1.0.6 (Sep 10, 2024) =
* Bump to WordPress 6.6.2
* Fixes calculate scale prices on products edit screen.
* Fixes some fields locations on Sale invoices.

= 1.0.5 (Jul 26, 2024) =
* Bump to WordPress 6.6 & PHP 8.1
* Compatibility with the new [Fakturo](https://wordpress.org/plugins/fakturo) FREE extension.
* Updated EDD plugin updater library.
* Fixes the creation of the menu pages to avoid PHP Warnings.
* Fixes Extension link on Settings Page.

= 1.0.4 (Feb 27, 2023) =
* IMPORTANT UPDATE: Fixes vulnerabilities in third-party libraries. 
* Updated DomPDF library.
* Added condition to avoid errors on templates variables.
* Fixes Addons page.
* Fixes Welcome page.
* Fixes many warnings and PHP notices.
* Fixes an error when is activated.

= 1.0.3 (Aug 2, 2022) =
* Updated Select2 libraries to last stable release 4.0.13 to improves security.
* Added .min versions of css and js files to improve debug and loadings speeds.
* Added i18n languages of select2 to allow compatibilities with other languages.
* Added Arabic background images for invoices and receipts.
* Improves Default Print templates for invoices and Receipts.
* Improves Receipt and invoices background images for English and Spanish.
* Fixes See PDF button inside print Print template edit screen.
* Fixes quick actions Send email to clients on Receipts list.
* Fixes removing CUIT from english background print template receipt.
* Fixes adding missed client variables on receipt print templates.
* Fixes not showed suggested prices on load product edit screen.

= 1.0.2 (Jul 28, 2022) =
* Added print template invoices and checks ArrayToLoop variables for Receipts.
* Added DejaVu fonts to dompdf for RTL languages.
* Updated Receipts default print template.
* Fixes Warning: number_format() expects parameter 1 to be float on print templates.
* Fixes to use different fonts in print templates for dompdf. 
* Fixes emailing PDF in UTF-8 to support RTL languages. 

= 1.0.1 (Jul 15, 2022) =
* Added background images variables in product, client and provider print templates.
* Added default background images selected by language.
* Added english & spanish images for document backgrounds.
* Fixes Reset to default Print templates for .

= 1.0.0 (Jun 29, 2022) =
* Added new reports.
* Added new print templates by default.
* Added reference codes to products list.
* Added print featured for Pendient invoices.
* Updated dompdf library.
* Tweaks on Quick Actions texts on sales.
* Changed Draft by Pending status texts in sales.
* Improved security nonces on Quick actions.
* Improved many Help contents.
* Fixes price scales on sales.
* Fixes available Bulk Actions on sales.
* Fixes Date Picker in Start of activities of Setting page to allow strings.
* Fixes deprecated call to wp_no_robots on wizard.
* Many fixes and improvements on all the code and styles here and there.

= 0.9.1 Beta (April 18, 2019) =
* Added Client Name in Sales list.
* Added order by Client and Date in Sales list.
* Added suport to translate the sliderchecks options.
* Added Date Picker in Start of activities of Setting page.
* Added Exit button in all screens of Configurations wizard.
* Added Fakturo Top Menu with the dashboard items.
* Added Dashboard Options to show or not Fakturo Top Menu.
* Added some security tweaks on AJAX calls.
* Added popups button to add Invoice types required by Tax Conditions fields.
* Added new helps to Products Scale Prices.
* Updates webcam snapshots libraries to take photos of articles/clients.
* Tweaks the code height textarea in print templates screen.
* Fixes title of Add New State Popup.
* Fixes notice when getting data of the provider in the product.
* Fixes css in wordpress dashboard widget.
* Fixes the menu order broken for some cases.
* Fixes fakturo_manager as minimun role to save Clients, Products, Providers and Sales.
* Fixes autoclose window on print documents.
* Fixes Symbol not saved in Invoice Types table.
* Fixes some warnings in receipt list.
* Fixes some warnings in extensions list.
* Fixes some issues in URL after save a posts type.
* Fixes some missed translations domains.
* Updated Spanish Language.

= 0.9 Beta =
* Added help tips for all screens
* Added languages files and spanish language. 
* Fixed an issue on products.
* Fixed some PHP warnings in settings page.
* Added notices when the settings are updated.
* Fixed issue on Firefox that doesn't redirect to next page.
* Fixed some issue in the dashboard widget.
* Some tweaks in performance on load countries and states in wizard.
* Fixed issue on control of duplicates for invoice numbers. 

= 0.8 Beta =
* Fixed issue on user list.
* Fixed issue on reset to default button of print templates.
* Added client phone on print templates and email template.
* Added a function to convert numbers to letters.

= 0.7 Beta =
* Added reports page.
* Added the capabilities of the report pages and sections.
* Added Client's account report.
* Wizard install
* Loading all countries and states on wizard install.
* Company info on wizard install.
* Loading all currencies on wizard install.
* Money Format on wizard install.
* Invoice Details and Formats on wizard install.
* Products on wizard install.
* Payments on wizard install.

= 0.6 Beta =
* Added default contents to emails and print templates instead to begin from scratch.
* Added warning notices about missing and required settings.
* Minify plugin size in 70% deleting unuseful fonts from PDF.
* Added get_fakturo_term_{taxonomy} filter.
* Added icons on dashboard by user.
* Fixed issues on Addons Page.
* Added Redirects with Conditions.
* Some changes on activate & deactivate for better performance.
* Hide Taxonomies and Custom Post types as invoices from front-en changing its public value to false.
* Fixes lot of issues, warnings and errors.

= 0.5 Beta =
* Added Custom Dashboard with widgets with the most used links and some summaries.
* Added Default templates for print templates to avoid creation from scratch.
* Added Default templates for email templates to avoid creation from scratch.
* Added tips and texts to the Help Tabs in all the system screens.
* Added redirection to the list when saves a custom term of Products. (WP>=4.6)
* Fixes on quick actions for invoices.
* Some Fixes on invoice editing.

= 0.4 Beta =
* Added print invoices.
* Added send invoices as PDF attached in emails to the clients.
* Fixes some filters on clients CPT.
* Fixes a PHP Fatal error on Addons page.
* Cleaned the Print and email templates Select fields to allow only Documents.
* New Addon created to bill subscriptions.

= 0.3 Beta =
* Added Print templates with highlighter code textarea and previews.
* Added emails templates with WP Editor and Previews.
* Added New settings tab for System Settings.
* Added support to print documents as PDF.
* Added version control and re-assign permissions on updates.
* Added Help Screens in all Pages. (We are making the tutorials :)
* Added Welcome & changelog Pages.
* Added some screenshots.
* Added Notices class.
* Added Addons Page.
* Lot of fixes.

= 0.2 Beta =
* Added lot of Settings Screens.
* Added Providers and Clients.
* Added Products with Categories, Models.
* Added Stock movements and management.
* Added Sale Invoices.

= 0.1 Beta =
First Release


== Upgrade Notice ==
Improves all reports range and some bug fixes.