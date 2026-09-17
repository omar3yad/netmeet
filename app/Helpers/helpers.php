<?php

use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Cache;
use App\Models\Language;
use App\Models\Plan;
use App\Models\User;
use App\Models\Page;
use App\Models\LogActivity;

//get settings from the global config table
function getSetting($key) {
	$settings = Cache::rememberForever('settings', function () {
		return GlobalConfig::all()->pluck('value', 'key');
	});
	
	if (!$settings[$key]) {
		Cache::forget('settings');
		$settings = GlobalConfig::all()->pluck('value', 'key');
	}

	return $settings[$key];
}

//get features associated with the user ID
function getUserPlanFeatures($id) {
	$user = User::find($id);
    $planId = $user->plan_id;

    if ($user->plan_ends_at == '') {
        $planId = $user->plan_id;
    } else if (date('Y-m-d', strtotime($user->plan_ends_at)) < date('Y-m-d')) {
        $planId = 1;
    }

	return Plan::find($planId)->features;
}

//get languages
function getLanguages () {
	$languages = Cache::rememberForever('languages', function () {
		return Language::where(['status' => 'active'])->select('code', 'name', 'default', 'direction')->get();
	});

	return $languages;
}

//get selected language
function getSelectedLanguage () {
	if (session('locale')) {
        $selectedLanguage = getLanguages()->first(function($langauage) {
            return $langauage->code == session('locale');
        });

		if ($selectedLanguage) return $selectedLanguage;
	}

	return getDefaultLanguage();
}

//get default language
function getDefaultLanguage() {
	$languages = Cache::rememberForever('defaultLangauage', function () {
		return Language::where(['default' => 'yes'])->select('code', 'name', 'direction')->first();
	});

	return $languages;
}

//get value
function isInstalled () {
	return session('installed');
}

//check if the demo mode is enabled
function isDemoMode () {
	return config('app.demo_mode');
}

// Format money.
function formatMoney($amount, $currency)
{
    if (in_array(strtoupper($currency), config('currencies.zero_decimals'))) {
        return number_format($amount, 0, __('.'), __(','));
    } else {
        return number_format($amount, 2, __('.'), __(','));
    }
}

// Get the enabled payment gateways.
function paymentGateways()
{
    $paymentGateways = config('payment.gateways');
    foreach ($paymentGateways as $key => $value) {
        if (!getSetting($key)) {
            unset($paymentGateways[$key]);
        }
    }

    return $paymentGateways;
}

function calculateInclusiveTax($amount, $discount, $inclusiveTaxRate, $inclusiveTaxRates)
{
    return calculatePostDiscount($amount, $discount) * ($inclusiveTaxRate / 100);
}

/**
 * Calculate the total, including the exclusive taxes.
 * PostDiscount + ExclusiveTax$
 */
function checkoutTotal($amount, $discount, $exclusiveTaxRates, $inclusiveTaxRates)
{	
    return calculatePostDiscount($amount, $discount) + (calculatePostDiscount($amount, $discount) * ($exclusiveTaxRates / 100));
}

/**
 * Returns the amount after discount.
 * Amount - Discount$
 */
function calculatePostDiscount($amount, $discount)
{
    return $amount - calculateDiscount($amount, $discount);
}

/**
 * Returns the exclusive tax amount.
 * PostDiscountLessInclTaxes * TaxRate
 */
function checkoutExclusiveTax($amount, $discount, $exclusiveTaxRate, $inclusiveTaxRates)
{
    // return calculatePostDiscountLessInclTaxes($amount, $discount, $inclusiveTaxRates) * ($exclusiveTaxRate / 100);
    return calculatePostDiscount($amount, $discount) * ($exclusiveTaxRate / 100);
}

/**
 * Returns the discount amount.
 * Amount * Discount%
 */
function calculateDiscount($amount, $discount)
{
    return $amount * ($discount / 100);
}

/**
 * Returns the amount after discount and included taxes.
 * PostDiscount - InclusiveTaxes$
 */
function calculatePostDiscountLessInclTaxes($amount, $discount, $inclusiveTaxRates)
{
    return calculatePostDiscount($amount, $discount) - calculateInclusiveTaxes($amount, $discount, $inclusiveTaxRates);
}

/**
 * Returns the inclusive taxes amount.
 * PostDiscount - PostDiscount / (1 + TaxRate)
 */
function calculateInclusiveTaxes($amount, $discount, $inclusiveTaxRate)
{
    return calculatePostDiscount($amount, $discount) - (calculatePostDiscount($amount, $discount) / (1 + ($inclusiveTaxRate / 100)));
}

//get pages to show in footer
function getPages()
{
    return Page::select('title', 'slug')->where('footer', 'yes')->get();
}

//format date
function formatDate($date)
{
    return $date ? date('d-m-Y', strtotime($date)) : '-';
}

//format time
function formatTime($time)
{
    return $time ? date('h:i A', strtotime($time)) : '-';
}

//paystack/mollie/razorpay api requests through curl
function callCurlApiRequest($endpoint,$method,$params = null,$gateway = 'paystack'){
    try{
        if($gateway == 'paystack'){
            $ApiUrl = "https://api.paystack.co";
            $Secret = getSetting('PAYSTACK_SECRET_KEY');
        }elseif($gateway == 'razorpay'){
            $ApiUrl = "https://api.razorpay.com/v1";
            $Key = getSetting('RAZORPAY_API_KEY');
            $Secret = getSetting('RAZORPAY_SECRET_KEY');
            $base64encode = base64_encode($Key.":".$Secret);
        }else{
            $ApiUrl = "https://api.mollie.com";
            $Secret = getSetting('MOLLIE_API_KEY');
        }
        

    $ch = curl_init();            
    if($method == 'GET'){
        if($gateway == 'razorpay'){
            curl_setopt_array($ch, array(
                CURLOPT_URL => $ApiUrl.$endpoint,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic {$base64encode}",
                    "Cache-Control: no-cache",
                    ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));
        }else{
            curl_setopt_array($ch, array(
                CURLOPT_URL => $ApiUrl.$endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$Secret}",
                "Cache-Control: no-cache",
                ),
            ));
        }
    }else{
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $ApiUrl.$endpoint);
        if($method == 'PUT'){
            $fields_string = http_build_query($params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        }elseif($method == 'PATCH'){
            $fields_string = http_build_query($params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        }elseif($method == 'DELETE'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }else{
            $fields_string = http_build_query($params);
            curl_setopt($ch,CURLOPT_POST, true);    
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        }       
        
        if($gateway == 'razorpay'){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic {$base64encode}",
                "Cache-Control: no-cache",
            ));
        }else{
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer {$Secret}",
                "Cache-Control: no-cache",
            ));
        }
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

    }
    
    $response = curl_exec($ch);
    $response_array = json_decode($response,true);
    return $response_array;
    }catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

//export into csv
function exportToCSV($data, $csvFileName){
    $columnExtract = json_decode($data[0],true);
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
    ];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $csvFileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $handle = fopen('php://output', 'w');
    $columns = array_keys($columnExtract);
    foreach($columns as $key => $val){
        $columns[$key] = ucfirst($val);
    }
    
    fputcsv($handle, $columns); // Add more headers as needed
    foreach ($data as $val) {
        $val = json_decode($val,true);
        fputcsv($handle, $val);
    }
    
    fclose($handle);
    
    return $headers;
}

//log all activities
function logActivity($primary, $model, $eventtype, $log)
{
    LogActivity::insert([
        'primary_id'    => $primary,
        'user_id'       => auth()->user() ? auth()->user()->id : $primary,
        'model'         => $model,
        'event_type'    => $eventtype,
        'log'           => $log,
        'ip'            => request()->ip(),
    ]);   
}

//get version number without the dots
function getVersion() {
    return str_replace('.', '', getSetting('VERSION'));
}

//get auth user info
function getAuthUserInfo($property) {
    return auth()->user() ? auth()->user()->$property : '';
}

//check if the upgrade button should be displayed or not
function showUpgrade() {
    $totalActivePlans = Plan::where(['status' => 1])->count();
    $userCurrentPlan =  auth()->user()->plan_id;

    return count(paymentGateways()) != 0 && getSetting('PAYMENT_MODE') == 'enabled' && $userCurrentPlan < $totalActivePlans;
}
