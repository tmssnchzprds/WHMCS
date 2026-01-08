<?php

const MODULE_NAME = "ResellerClubDomains";

const PROVISIONING_MODULE_IDENTIFIER = 'resellerclubtitan';
const ADDON_MODULE_IDENTIFIER = 'resellerclubdomains';

const AUTHOR = "ResellerClub.";
const MODULE_VERSION = "1.0";
const MODULE_ENVIRONMENT = "production"; // production/staging
const DEBUG_MODE = true; // @TODO turn to false for production

const CONFIG_DONT_IMPORT_DOMAINS = "dont_import_domains";
const CONFIG_DONT_IMPORT_CUSTOMERS = "dont_import_customers";
const CONFIG_DONT_EXPORT_CLIENTS = "dont_export_clients";
const CONFIG_DONT_SEND_RAA_REPORT = "dont_send_raa_report";
const CONFIG_DEFAULT_PAYMENT_METHOD = "default_payment_method";
const CONFIG_SETUP_TITAN_EMAIL_PRODUCT = "setup_titan_email_product";
const CONFIG_RESELLER_ID = "orderbox_reseller_id";
const CONFIG_RESELLER_API_KEY = "orderbox_api_key";
const TBL_CRONS = "ob_crons";
const TBL_CUSTOMERS = "ob_customers";
const TBL_DOMAINS = "ob_domains";
const TBL_ACTIONS = "ob_actions";
const TBL_CONFIGS = "ob_configs";
const DB_STATUS_ADDED = "added";
const DB_STATUS_STARTED = "started";
const DB_STATUS_COMPLETED = "completed";
const CRON_BATCH_SIZE = 100;
const CRON_TYPE_IMPORT_CUSTOMERS = "import_customers";
const CRON_TYPE_SYNC_CLIENTS = "sync_clients";
const CRON_TYPE_EXPORT_CLIENTS = "export_clients";
const CRON_TYPE_IMPORT_DEBIT_BALANCE = "import_debit_balance"; // @TODO TBD
const CRON_TYPE_IMPORT_DOMAINS = "import_domains";
const CRON_TYPE_IMPORT_DOMAINS_PER_CLIENT = "import_domains_per_client";
const CRON_TYPE_SYNC_DOMAINS_PER_CLIENTS = "sync_domains_per_client";
const CRON_TYPE_IMPORT_DOMAIN_DETAILS = "import_domain_details";
const CRON_TYPE_SYNC_DOMAINS = "sync_domains";
//const CRON_BUFFER_IN_SECONDS = 604800; // 7 days in seconds
const CRON_BUFFER_IN_SECONDS = 18144000; // 210 days in seconds
const REGISTRAR_RESELLERCLUB = "resellerclub";
const REGISTRAR_RESELLERCLUB_SETTING_NAME_RESELLER_ID = "ResellerID";
const REGISTRAR_RESELLERCLUB_SETTING_NAME_API_KEY = "APIKey";

const OB_URL_HTTP_API = "https://httpapi.com"; // @TODO also show as a configurable option for the admin?
const OB_CUSTOMER_STATUS_ACTIVE = "Active";
const OB_CUSTOMER_STATUS_SUSPENDED = "Suspended";
const OB_DOMAIN_STATUS_PENDING_VERIFICATION = "Pending Verification";
const WHMCS_CLIENT_STATUS_INACTIVE = "Inactive";
const CUSTOM_FIELD_OB_CUSTOMER_ID = "ob_customer_id";

const DEFAULT_LANGUAGE_CODE = "en";
const LANGUAGE_ISO_CODES = [
    "arabic" => "ar",
    "azerbaijani" => "az",
    "catalan" => "ca",
    "chinese" => "zh",
    "croatian" => "hr",
    "czech" => "cs",
    "danish" => "da",
    "dutch" => "nl",
    "english" => "en",
    "estonian" => "et",
    "farsi" => "fa",
    "french" => "fr",
    "german" => "de",
    "hebrew" => "he",
    "hungarian" => "hu",
    "italian" => "it",
    "macedonian" => "mk",
    "norwegian" => "no",
    "portuguese-br" => "pt-br",
    "portuguese-pt" => "pt",
    "romanian" => "ro",
    "russian" => "ru",
    "spanish" => "es",
    "swedish" => "sv",
    "turkish" => "tr",
    "ukranian" => "uk",
];
const ROLLBAR_ACCESS_TOKEN_POST_SERVER_ITEM = "8ca034e0bf4949a080ad1ac1df0c911d";
const OB_COUNTRY_CODE_INDIA = "IN";
const OB_INVOICE_OPTION_NO_INVOICE = "NoInvoice";


// ------------------------- Product Configs --------------------

const PRODUCT_GROUP_NAME = "ResellerClub Products";

/// NOT USED
//const PRODUCT_CONFIG_GROUP_NAME_TITAN = "Titan Product Config Group";
//const PRODUCT_CONFIG_GROUP_NAME_TITAN_FREE = "Titan Free Trial Product Config Group";


const CONFIG_OB_RESELLERCLUB_PRODUCT_GROUP_ID = "productid_groupid";
const CONFIG_OB_RESELLERCLUB_MAP_PLAN_PRODUCT = "planid_productid";
const CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANS = "productkey_plans";
const CONFIG_OB_RESELLERCLUB_PRODUCT_KEY_PLANID_FREE = "productkey_planid_free";

// -------------------------/Product Configs --------------------

// ------------------------- Titan Configs --------------------

const OB_PRODUCT_KEY_TITANEMAIL_GLOBAL = "titanmailglobal";
const OB_PRODUCT_KEY_TITANEMAIL_INDIA = "titanmailindia";

// Orderbox Plan IDs for Titan Global
const OB_PLAN_ID_TITAN_GLOBAL = [
    'professional' => 1762,
    'business_free' => 1755,
    'business' => 1756,
    'enterprise' => 1757
];

const OB_PLAN_ID_TITAN_INDIA = [
    'professional' => 1761,
    'business_free' => 1760,
    'business' => 1758,
    'enterprise' => 1759
];

// Orderbox plan upgrade map for Titan
const OB_PLAN_ID_UPGRADE_MAP_TITAN = [
    OB_PLAN_ID_TITAN_GLOBAL['professional'] => [OB_PLAN_ID_TITAN_GLOBAL['business']],
    OB_PLAN_ID_TITAN_GLOBAL['business'] => [OB_PLAN_ID_TITAN_GLOBAL['enterprise']],
    OB_PLAN_ID_TITAN_GLOBAL['business_free'] => [
        OB_PLAN_ID_TITAN_GLOBAL['business'],
        OB_PLAN_ID_TITAN_GLOBAL['enterprise']
    ],

    OB_PLAN_ID_TITAN_INDIA['professional'] => [OB_PLAN_ID_TITAN_INDIA['business']],
    OB_PLAN_ID_TITAN_INDIA['business'] => [OB_PLAN_ID_TITAN_INDIA['enterprise']],
    OB_PLAN_ID_TITAN_INDIA['business_free'] => [
        OB_PLAN_ID_TITAN_INDIA['business'],
        OB_PLAN_ID_TITAN_INDIA['enterprise']
    ],
];

//const PRODUCT_CONFIG_GROUP_NAME_TITAN = [
//    OB_PLAN_ID_TITAN_GLOBAL['professional'] => "Titan Professional",
//    OB_PLAN_ID_TITAN_GLOBAL['business'] => "Titan Business Free",
//    OB_PLAN_ID_TITAN_GLOBAL['business_free'] => "Titan Business",
//    OB_PLAN_ID_TITAN_GLOBAL['enterprise'] => "Titan Enterprise",
//
//    OB_PLAN_ID_TITAN_INDIA['professional'] => "Titan Professional",
//    OB_PLAN_ID_TITAN_INDIA['business'] => "Titan Business Free",
//    OB_PLAN_ID_TITAN_INDIA['business_free'] => "Titan Business",
//    OB_PLAN_ID_TITAN_INDIA['enterprise'] => "Titan Enterprise",
//];
//
//const PRODUCT_CONFIG_OPTION_SUB_TITAN = [
//    OB_PLAN_ID_TITAN_GLOBAL['professional'] => "5 GB per account",
//    OB_PLAN_ID_TITAN_GLOBAL['business'] => "10 GB per account",
//    OB_PLAN_ID_TITAN_GLOBAL['business_free'] => "10 GB per account",
//    OB_PLAN_ID_TITAN_GLOBAL['enterprise'] => "50 GB per account",
//
//    OB_PLAN_ID_TITAN_INDIA['professional'] => "5 GB per account",
//    OB_PLAN_ID_TITAN_INDIA['business'] => "10 GB per account",
//    OB_PLAN_ID_TITAN_INDIA['business_free'] => "10 GB per account",
//    OB_PLAN_ID_TITAN_INDIA['enterprise'] => "50 GB per account",
//];

const PRODUCT_CONFIG_GROUP_NAME_TITAN = [
    'professional' => "Titan Professional",
    'business_free' => "Titan Business Free",
    'business' => "Titan Business",
    'enterprise' => "Titan Enterprise"
];

const PRODUCT_CONFIG_OPTION_SUB_TITAN = [
    'professional' => "5 GB per account",
    'business_free' => "10 GB per account",
    'business' => "10 GB per account",
    'enterprise' => "50 GB per account"
];
const PRODUCT_CONFIG_OPTION_NAME_TITAN = "No. of email accounts";
const PRODUCT_CONFIG_OPTION_TYPE_QUANTITY = 4; // @TODO figure out if WHMCS stores these type->id map somewhere

const OB_NO_OF_ACCOUNTS_TITAN_FREE = 2;
const OB_NO_OF_MONTHS_TITAN_FREE = 3;

// -------------------------/Titan Configs --------------------

const OB_API_SLUGS = [
    "orderId",
    "productKey",
    "planId",
    "seats",
    "tenure"
];



