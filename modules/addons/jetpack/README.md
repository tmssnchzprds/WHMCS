# Jetpack WHMCS Provisioning Module
The Jetpack [WHMCS provisioning module](https://developers.whmcs.com/provisioning-modules/) is intended to assist Jetpack hosting partners who utilize WHMCS in managing Jetpack products for their users. The modules option is available when a product or product addon is created that implements the Jetpack WHMCS provisioning module in the list of available modules.

Once a purchase for a Jetpack product has been completed through WHMCS the Jetpack license can be issued based on one of the 4 available options in WHMCS when creating a new product that involves a module for provisioning(3 Automatic provisioning options and the option to not provision under Module Settings when creating a product).

The provisioning module uses standard WHMCS [functions](https://developers.whmcs.com/provisioning-modules/supported-functions/) for creating and terminating accounts to issue and revoke Jetpack licenses using the Jetpack Licensing API. Module functions include both automattic license issuing on checkout as well as the option to both issue and revoke a license using the create and terminate options available with the module.

## Getting Started
If you’d like to become a Jetpack Hosting Partner please take a look at the [Jetpack Hosting Partner Information Page](https://jetpack.com/for/hosts/) for more information and to get started. In order to use the module a valid WHMCS license as well as a Jetpack Hosting Partner account and API token are required. To get started with WHMCS please visit https://www.whmcs.com/.

## Setup
To install this module clone this repository in a directory named `jetpack` in the `modules/servers` directory. A server is not required for the module to function so the module will not be listed in the WHMCS Servers under the Setup tab for Products/Servers. Once the jetpack directory has been created with the module files the module is ready for use with new or existing products.

## Usage
To use the provisioning module create a new product or product addon in one of your existing product groups. As part of the product creation process in the Module Settings tab, select the Jetpack Provisioning module to get started. You will need to have an established Jetpack Hosting Partner account and your partner API token will be requested in order to utilize the Jetpack licensing API. The provisioning module will create a database table `jetpack_product_licenses` on it's first use if the table has not previously been created either by the [addon module](https://github.com/Automattic/jetpack-whmcs-addon-module) or previous use of the provisioning module.

## Configuration Options
The provisioning module has 2 configuration options
- API Token: Your API token mentioned above which is required for using the Jetpack licensing API.
- Jetpack Product: The Jetpack product that will be tied to the product being created/sold in WHMCS
- Show User License: If enabled the license key and instructions for setting up Jetpack will be displayed in the WHMCS user client area.




