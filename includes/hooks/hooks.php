<?php
session_start();
use WHMCS\Database\Capsule;
add_hook('ClientAreaPage', 1, function ($vars) {



        function get_product_price_by_id($product_id, $payment_period){
            $prices = Capsule::table('tblpricing')
                        ->where(['type' => 'product', 'relid' => $product_id])
                        ->orderBy('id', 'desc')
                        ->get();
            $price = $prices[0]->monthly;
            return $price;
        }


        


        function get_product_slug_by_id($product_id){
            $slugs = Capsule::table('tblproducts_slugs')
                        ->where(['product_id' => $product_id])
                        ->orderBy('id', 'desc')
                        ->get();
            $slug = $slugs[0]->slug;
            return $slug;
        }


        function get_product_group_slug_by_id($product_id){
            $slugs = Capsule::table('tblproducts_slugs')
                        ->where(['product_id' => $product_id])
                        ->orderBy('id', 'desc')
                        ->get();
            $slug = $slugs[0]->group_slug;
            return $slug;
        }

        function get_default_currency(){
            $default_currency = Capsule::table('tblcurrencies')
                        ->where('default', '1')
                        ->orderBy('id', 'desc')
                        ->get();
            return $default_currency[0];
        }

        function get_currency_by_id($currency_id){
            $default_currency = Capsule::table('tblcurrencies')
                        ->where('id', $currency_id)
                        ->orderBy('id', 'desc')
                        ->get();
            return $default_currency[0];
        }


        function get_current_currency_prefix(){
            $currency_id = get_default_currency()->id;
            
            if(isset($_REQUEST['currency'])){
                $currency_id = $_REQUEST['currency'];
            }else if(isset($_SESSION['currency'])){
                $currency_id = $_SESSION['currency'];
            }
            return get_currency_by_id($currency_id)->prefix;
        }

        function get_hosting_pricing_table($payment_period){

            $currency_id = get_default_currency()->id;
            
            if(isset($_REQUEST['currency'])){
                $currency_id = $_REQUEST['currency'];
            }else if(isset($_SESSION['currency'])){
                $currency_id = $_SESSION['currency'];
            }
            
            $hosting_products = Capsule::table('tblproducts')
                                    
                                    ->leftJoin('tblproducts_slugs', 'tblproducts.id', '=', 'tblproducts_slugs.product_id')
                                    ->leftJoin('tblpricing', 'tblproducts.id', '=', 'tblpricing.relid')
                                    ->select('tblproducts.*','tblproducts_slugs.*', 'tblpricing.*')
                                    ->where('tblpricing.type', 'product')
                                    ->where('tblpricing.currency', $currency_id)
                                    ->where('tblproducts.type', 'hostingaccount')
                                    ->where('tblproducts.hidden','0')
                                    ->groupBy('tblproducts.order')
                                    ->get();

            $hosting_products = json_decode($hosting_products, true);

            return $hosting_products;
        }
        
            
        $hosting_products = get_hosting_pricing_table('monthly');


        function get_product_groups(){
            $groups = Capsule::table('tblproductgroups')
                        ->where(['hidden' => '0'])
                        ->orderBy('id', 'desc')
                        ->get();
            
            return $groups;
        }

        function get_app_settings($setting_name){
            $configuration = Capsule::table('tblconfiguration')
                        ->where(['setting' => $setting_name])
                        ->orderBy('id', 'desc')
                        ->get();
            
        //$configuration = json_decode($configuration, true);
        return $configuration;
        }

        function get_company_address(){
            $configuration = get_app_settings('InvoicePayTo');
            return $configuration[0]->value;
        }

       

        $results = array(
            "hosting_products" => $hosting_products,
            'default_currency_prefix' => get_current_currency_prefix(),
            'products_groups' => get_product_groups(),
            'company_address' => get_company_address(),
        );

        return $results;
    
});


