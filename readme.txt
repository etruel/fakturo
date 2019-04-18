=== Fakturo ===
Contributors: etruel, sniuk, khaztiel
Donate link: 
Tags: invoice, sales, purchases, billing, receipt, Tax Code, VAT, PDF invoices, bill, bill clients, merchantplus, SME, pyme, checkout, online payment, pay, recurring billing, send invoice, web invoice, wp-invoice, wp-sales, e-commerce, e-store, eshop, wp ecommerce
Requires at least: 5.0
Tested up to: 5.1.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The better open source Invoice System.  An easy and fully-configurable SME Management system for Wordpress.

== Description ==

Fakturo is a complete system SME management, released as a Wordpress plugin with multiple functions for small and medium-sized companies.
It's FREE, fully-configurable, customizable and upgradable through Wordpress filters and also by AddOns or customized support.

NOTE: This plugin is in Beta stage. The 1.0 version will be the official release but you can start to use it now ;-)
The release date is close, we are working on it every day!!
We really enjoy and thank if you can check and test the Fakturo.
We are receiving feedbacks in english and spanish at the Wordpress support forums and in the [development repository](https://bitbucket.org/etruel/fakturo/issues).

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

= 0.9.1 Beta =
Many tweaks. 1.0 coming soon.