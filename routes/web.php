<?php
use App\Http\Controllers\RecordingController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (file_exists(storage_path('installed'))) {
    Auth::routes([
        'register' => getSetting('REGISTRATION') == 'enabled',
        'verify' => getSetting('VERIFY_USERS') == 'enabled'
    ]);
} else {
    Auth::routes();
}

//home route
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['tfa'])->prefix('profile')->group(function () {
    Route::get('/info', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.profile');
    Route::post('/info', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.profile.update');
    Route::post('/upload-avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar']);
    Route::post('/delete-avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar']);

    Route::get('/security', [App\Http\Controllers\ProfileController::class, 'security'])->name('profile.security');
    Route::post('/security', [App\Http\Controllers\ProfileController::class, 'updateSecurity']);

    Route::get('/plan', [App\Http\Controllers\ProfileController::class, 'myPlan'])->name('profile.plan');
    Route::post('/cancel-plan', [App\Http\Controllers\ProfileController::class, 'cancelPlan'])->name('cancelPlan');

    Route::get('/payments', [App\Http\Controllers\ProfileController::class, 'payments'])->name('profile.payments');
    Route::get('/invoice/{id}', [App\Http\Controllers\ProfileController::class, 'invoice'])->name('profile.invoice');
    Route::get('/api', [App\Http\Controllers\ProfileController::class, 'api'])->name('profile.api');

    //contact list routes and URLs
    Route::get('/contact', [App\Http\Controllers\ProfileController::class, 'contacts'])->name('profile.contacts');
    Route::get('/contact/create', [App\Http\Controllers\ProfileController::class, 'contactForm'])->name('profile.createContactForm');
    Route::get('/contact/edit/{id}', [App\Http\Controllers\ProfileController::class, 'editContactForm'])->name('profile.editContactForm');
    Route::post('/contact/create', [App\Http\Controllers\ProfileController::class, 'createContact'])->name('profile.createContact');
    Route::post('/contact/edit/{id}', [App\Http\Controllers\ProfileController::class, 'editContact'])->name('profile.editContact');
    Route::post('/contact/delete', [App\Http\Controllers\ProfileController::class, 'deleteContact'])->name('profile.deleteContact');
    Route::get('/contact/import', [App\Http\Controllers\ProfileController::class, 'contactImportForm'])->name('profile.importContactForm');
    Route::get('/contact/download', [App\Http\Controllers\ProfileController::class, 'downloadCsvFile'])->name('profile.downloadCsvFile');
    Route::post('/contact/import-contact', [App\Http\Controllers\ProfileController::class, 'importContact'])->name('profile.importContact');
    Route::get('/tfa', [App\Http\Controllers\ProfileController::class, 'tfa'])->name('profile.tfa');
    Route::post('/tfa', [App\Http\Controllers\ProfileController::class, 'updateTfa'])->name('profile.updateTfa');
    Route::get('/linkrazorpay', [App\Http\Controllers\ProfileController::class, 'linkRazorpay'])->name('profile.linkrazorpay');
});

//check if auth mode is enabled
Route::middleware('checkAuthMode')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/get-all-meetings', [App\Http\Controllers\DashboardController::class, 'getAllMeetings'])->name('dashboard.getAllMeetings');
    Route::get('dashboard/get-meeting/{id}', [App\Http\Controllers\DashboardController::class, 'getMeetingById'])->name('dashboard.getMeetingById');
});

//admin routes
Route::middleware(['tfa', 'auth', 'checkAdmin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
    Route::get('update', [App\Http\Controllers\AdminController::class, 'update'])->name('update');
    Route::get('check-for-update', [App\Http\Controllers\AdminController::class, 'checkForUpdate']);
    Route::get('download-update', [App\Http\Controllers\AdminController::class, 'downloadUpdate']);
    Route::get('license', [App\Http\Controllers\AdminController::class, 'license'])->name('license');
    Route::get('verify-license', [App\Http\Controllers\AdminController::class, 'verifyLicense']);
    Route::get('uninstall-license', [App\Http\Controllers\AdminController::class, 'uninstallLicense']);
    Route::get('signaling', [App\Http\Controllers\AdminController::class, 'signaling'])->name('signaling');
    Route::get('check-signaling', [App\Http\Controllers\AdminController::class, 'checkSignaling']);

    //meeting routes
    Route::get('meetings', [App\Http\Controllers\MeetingController::class, 'index'])->name('meetings');
    Route::post('update-meeting-status', [App\Http\Controllers\MeetingController::class, 'updateMeetingStatus']);
    Route::post('delete-meeting-admin', [App\Http\Controllers\MeetingController::class, 'deleteMeeting']);
    Route::get('meetings/search', [App\Http\Controllers\MeetingController::class, 'searchMeeting'])->name('meeting.search');
    Route::get('meetings/export', [App\Http\Controllers\MeetingController::class, 'exportMeeting'])->name('meetings.export');

    //user routes
    Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users');
    Route::post('update-user-status', [App\Http\Controllers\UserController::class, 'updateUserStatus']);
    Route::post('delete-user', [App\Http\Controllers\UserController::class, 'deleteUser']);
    Route::get('users/create', [App\Http\Controllers\UserController::class, 'createUserForm'])->name('createUser');
    Route::post('create-user', [App\Http\Controllers\UserController::class, 'createUser'])->name('storeUser');
    Route::get('users/search', [App\Http\Controllers\UserController::class, 'searchUser'])->name('user.search');
    Route::post('users/assign-plan', [App\Http\Controllers\UserController::class, 'assignPlan']);
    Route::get('users/export', [App\Http\Controllers\UserController::class, 'exportUser'])->name('user.export');
    

    //global config routes
    Route::get('global-config', [App\Http\Controllers\GlobalConfigController::class, 'index'])->name('global-config');
    Route::get('global-config/edit/{id}', [App\Http\Controllers\GlobalConfigController::class, 'edit']);
    Route::post('update-global-config', [App\Http\Controllers\GlobalConfigController::class, 'updateBasic'])->name('basic.update');
    Route::get('global-config/application', [App\Http\Controllers\GlobalConfigController::class, 'application'])->name('global-config.application');
    Route::post('global-config/application', [App\Http\Controllers\GlobalConfigController::class, 'updateApplication'])->name('application.update');
    Route::get('global-config/company', [App\Http\Controllers\GlobalConfigController::class, 'company'])->name('global-config.company');
    Route::post('global-config/company', [App\Http\Controllers\GlobalConfigController::class, 'updateCompany'])->name('company.update');
    Route::get('global-config/meeting', [App\Http\Controllers\GlobalConfigController::class, 'meeting'])->name('global-config.meeting');
    Route::post('global-config/meeting', [App\Http\Controllers\GlobalConfigController::class, 'updateMeeting'])->name('meeting.update');
    Route::get('global-config/js', [App\Http\Controllers\GlobalConfigController::class, 'customJs'])->name('global-config.js');
    Route::post('global-config/js', [App\Http\Controllers\GlobalConfigController::class, 'updateJs'])->name('js.update');
    Route::get('global-config/css', [App\Http\Controllers\GlobalConfigController::class, 'customCss'])->name('global-config.css');
    Route::post('global-config/css', [App\Http\Controllers\GlobalConfigController::class, 'updateCss'])->name('css.update');
    Route::get('global-config/smtp', [App\Http\Controllers\GlobalConfigController::class, 'smtp'])->name('global-config.smtp');
    Route::post('global-config/smtp', [App\Http\Controllers\GlobalConfigController::class, 'updateSmtp'])->name('smtp.update');
    Route::get('global-config/api', [App\Http\Controllers\GlobalConfigController::class, 'api'])->name('global-config.api');
    Route::post('global-config/test-smtp', [App\Http\Controllers\GlobalConfigController::class, 'testSmtp'])->name('test.update');
    Route::get('global-config/recaptcha', [App\Http\Controllers\GlobalConfigController::class, 'recaptcha'])->name('global-config.recaptcha');
    Route::post('global-config/recaptcha', [App\Http\Controllers\GlobalConfigController::class, 'updateRecaptcha'])->name('recaptcha.update');
    Route::get('global-config/social-login', [App\Http\Controllers\GlobalConfigController::class, 'socialLogin'])->name('global-config.sociallogin');
    Route::post('global-config/social-login', [App\Http\Controllers\GlobalConfigController::class, 'updateSocialLoginSettings'])->name('sociallogin.update');

    //languages routes
    Route::get('languages', [App\Http\Controllers\LanguagesController::class, 'index'])->name('languages');
    Route::get('languages/add', [App\Http\Controllers\LanguagesController::class, 'create']);
    Route::post('create-language', [App\Http\Controllers\LanguagesController::class, 'createLanguage'])->name('createLanguage');
    Route::get('languages/edit/{id}', [App\Http\Controllers\LanguagesController::class, 'edit'])->name('languages.edit');
    Route::post('update-language/{id}', [App\Http\Controllers\LanguagesController::class, 'updateLanguage'])->name('updateLanguage');
    Route::post('languages/delete', [App\Http\Controllers\LanguagesController::class, 'deleteLanguage']);
    Route::get('languages/download-english', [App\Http\Controllers\LanguagesController::class, 'downloadEnglish']);
    Route::get('languages/download-file/{code}', [App\Http\Controllers\LanguagesController::class, 'downloadFile']);
    Route::get('languages/search', [App\Http\Controllers\LanguagesController::class, 'searchLanguage'])->name('languages.search');

    Route::get('activity-log', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-log');
    Route::get('activity-log/search', [App\Http\Controllers\ActivityLogController::class, 'searchActivityLog'])->name('activity-log.search');
    Route::get('activity-log/export', [App\Http\Controllers\ActivityLogController::class, 'exportActivityLog'])->name('activity-log.export');

    //coupons routes
    Route::get('/coupons', [App\Http\Controllers\CouponController::class, 'index'])->name('admin.coupons');
    Route::get('/coupons/new', [App\Http\Controllers\CouponController::class, 'create'])->name('admin.coupons.new');
    Route::get('/coupons/{id}/edit', [App\Http\Controllers\CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::post('/coupons/new', [App\Http\Controllers\CouponController::class, 'store']);
    Route::post('/coupons/{id}/edit', [App\Http\Controllers\CouponController::class, 'update']);
    Route::post('/update-coupon-status', [App\Http\Controllers\CouponController::class, 'updateStatus']);
    Route::get('coupons/search', [App\Http\Controllers\CouponController::class, 'searchCoupon'])->name('coupons.search');

    //plans routes
    Route::get('/plans', [App\Http\Controllers\PlanController::class, 'index'])->name('admin.plans');
    Route::get('/plans/new', [App\Http\Controllers\PlanController::class, 'create'])->name('admin.plans.new');
    Route::get('/plans/{id}/edit', [App\Http\Controllers\PlanController::class, 'edit'])->name('admin.plans.edit');
    Route::post('/plans/new', [App\Http\Controllers\PlanController::class, 'store']);
    Route::post('/plans/{id}/edit', [App\Http\Controllers\PlanController::class, 'update']);
    Route::post('/update-plan-status', [App\Http\Controllers\PlanController::class, 'updateStatus']);
    Route::get('plans/search', [App\Http\Controllers\PlanController::class, 'searchPlan'])->name('plan.search');

    //tax rates routes
    Route::get('/tax-rates', [App\Http\Controllers\TaxRateController::class, 'index'])->name('admin.tax_rates');
    Route::get('/tax-rates/new', [App\Http\Controllers\TaxRateController::class, 'create'])->name('admin.tax_rates.new');
    Route::get('/tax-rates/{id}/edit', [App\Http\Controllers\TaxRateController::class, 'edit'])->name('admin.tax_rates.edit');
    Route::post('/tax-rates/new', [App\Http\Controllers\TaxRateController::class, 'store']);
    Route::post('/tax-rates/{id}/edit', [App\Http\Controllers\TaxRateController::class, 'update']);
    Route::post('/update-tax-rates-status', [App\Http\Controllers\TaxRateController::class, 'updateStatus']);
    Route::get('/tax-rates/search', [App\Http\Controllers\TaxRateController::class, 'searchTaxrates'])->name('tax_rates.search');

    //payment process routes
    Route::get('/payment-gateways', [App\Http\Controllers\GlobalConfigController::class, 'paymentGateways'])->name('admin.payment_gateways');
    Route::post('/payment-gateways', [App\Http\Controllers\GlobalConfigController::class, 'updatePaymentGateways'])->name('admin.payment_gateways');

    //pages admin routes
    Route::get('/pages', 'PageController@index')->name('pages');
    Route::get('pages/add', [App\Http\Controllers\PageController::class, 'create']);
    Route::post('create-page', [App\Http\Controllers\PageController::class, 'createPage'])->name('createPage');
    Route::get('pages/edit/{id}', [App\Http\Controllers\PageController::class, 'edit']);
    Route::post('update-page/{id}', [App\Http\Controllers\PageController::class, 'updatePage'])->name('updatePage');
    Route::post('pages/delete', [App\Http\Controllers\PageController::class, 'deletePage']);

    Route::get('/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('admin.emailTemplates');
    Route::get('/email-templates/edit/{id}', [App\Http\Controllers\EmailTemplateController::class, 'edit']);
    Route::post('update-email-template/{id}', [App\Http\Controllers\EmailTemplateController::class, 'updateEmailTemplate'])->name('updateEmailTemplate');

    //payment listing
    Route::get('transaction', [App\Http\Controllers\TransactionController::class, 'index'])->name('admin.transaction');
    Route::get('transaction/search', [App\Http\Controllers\TransactionController::class, 'searchTransactions'])->name('transactions.search');
    Route::get('transaction/export', [App\Http\Controllers\TransactionController::class, 'exportTransaction'])->name('transaction.export');

    Route::get('/pages/search', [App\Http\Controllers\EmailTemplateController::class, 'emailTempl'])->name('pages.search');
});

//checkout routes
Route::middleware(['auth', 'checkPaymentMode', 'tfa'])->prefix('checkout')->group(function () {
    Route::get('/cancelled', [App\Http\Controllers\CheckoutController::class, 'cancelled'])->name('checkout.cancelled');
    Route::get('/pending', [App\Http\Controllers\CheckoutController::class, 'pending'])->name('checkout.pending');
    Route::get('/complete', [App\Http\Controllers\CheckoutController::class, 'complete'])->name('checkout.complete');

    Route::get('/{id}', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/{id}', [App\Http\Controllers\CheckoutController::class, 'process']);
});

//general routes
Route::post('create-meeting', [App\Http\Controllers\DashboardController::class, 'createMeeting']);
Route::post('delete-meeting', [App\Http\Controllers\DashboardController::class, 'deleteMeeting']);
Route::post('edit-meeting', [App\Http\Controllers\DashboardController::class, 'editMeeting']);
Route::post('send-invite', [App\Http\Controllers\DashboardController::class, 'sendInvite']);
Route::get('get-invites', [App\Http\Controllers\DashboardController::class, 'getInvites']);
Route::get('meeting/{id}', [App\Http\Controllers\DashboardController::class, 'meeting'])->name('meeting');
Route::get('widget', [App\Http\Controllers\DashboardController::class, 'widget']);
Route::post('check-meeting', [App\Http\Controllers\DashboardController::class, 'checkMeeting']);
Route::post('check-meeting-password', [App\Http\Controllers\DashboardController::class, 'checkMeetingPassword']);
Route::post('get-details', [App\Http\Controllers\DashboardController::class, 'getDetails']);
Route::get('languages/{locale}', [App\Http\Controllers\DashboardController::class, 'setLocale'])->name('language');
Route::get('/check-details', [App\Http\Controllers\DashboardController::class, 'checkDetails']);

//pages routes
Route::get('/pages/{id}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');
Route::get('/pricing', [App\Http\Controllers\PricingController::class, 'index'])->name('pricing');

//webhook routes
Route::post('webhooks/stripe', [App\Http\Controllers\WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('webhooks/paypal', [App\Http\Controllers\WebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::post('webhooks/paystack', [App\Http\Controllers\WebhookController::class, 'paystack'])->name('webhooks.paystack');
Route::post('webhooks/mollie', [App\Http\Controllers\WebhookController::class, 'mollie'])->name('webhooks.mollie');
Route::post('webhooks/razorpay', [App\Http\Controllers\WebhookController::class, 'razorpay'])->name('webhooks.razorpay');
Route::post('webhooks/meeting', [App\Http\Controllers\WebhookController::class, 'meeting'])->name('webhooks.meeting');
Route::post('webhooks/user', [App\Http\Controllers\WebhookController::class, 'user'])->name('webhooks.user');

//Paytack callback
Route::get('/paystack/callback', [App\Http\Controllers\WebhookController::class, 'handlePaystackGatewayCallback'])->name('callback.paystack');
Route::get('/mollie/callback', [App\Http\Controllers\WebhookController::class, 'handleMollieGatewayCallback'])->name('callback.mollie');

Route::middleware(['auth'])->group(function () {
    //two factor authentication routes
    Route::get('two-factor-auth', [App\Http\Controllers\TwoFAController::class, 'index'])->name('tfa.index');
    Route::post('two-factor-auth', [App\Http\Controllers\TwoFAController::class, 'store'])->name('tfa.post');
    Route::get('two-factor-auth/resend', [App\Http\Controllers\TwoFAController::class, 'resend'])->name('tfa.resend');
});

Route::middleware(['guest'])->group(function () {
    //Google
    Route::get('/login/google', [App\Http\Controllers\SocialLoginController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/login/google/callback', [App\Http\Controllers\SocialLoginController::class, 'handleGoogleCallback'])->name('login.google.callback');
    //Facebook
    Route::get('/login/facebook', [App\Http\Controllers\SocialLoginController::class, 'redirectToFacebook'])->name('login.facebook');
    Route::get('/login/facebook/callback', [App\Http\Controllers\SocialLoginController::class, 'handleFacebookCallback'])->name('login.facebook.callback');
    //Linkedin
    Route::get('/login/linkedin', [App\Http\Controllers\SocialLoginController::class, 'redirectToLinkedin'])->name('login.linkedin');
    Route::get('/login/linkedin/callback', [App\Http\Controllers\SocialLoginController::class, 'handleLinkedinCallback'])->name('login.linkedin.callback');
    //Twitter
    Route::get('/login/twitter', [App\Http\Controllers\SocialLoginController::class, 'redirectToTwitter'])->name('login.twitter');
    Route::get('/login/twitter/callback', [App\Http\Controllers\SocialLoginController::class, 'handleTwitterCallback'])->name('login.twitter.callback');
});

//add username page when login/register through social login
Route::get('add-username', [App\Http\Controllers\Auth\RegisterController::class,'username'])->name('username.add');
Route::post('add-username/verify', [App\Http\Controllers\Auth\RegisterController::class,'usernameVerify'])->name('username.add.verify');
Route::post('/upload-video', [\App\Http\Controllers\VideoController::class, 'upload']);
// routes/web.php
Route::get('/my-recordings', [RecordingController::class, 'index'])->name('recordings.index');
Route::delete('/recordings/{id}', [\App\Http\Controllers\RecordingController::class, 'destroy'])->name('recordings.destroy');

use App\Http\Controllers\VideoController;

Route::post('/upload-video', [\App\Http\Controllers\VideoController::class, 'upload']);

// ???? ???? ??????? ???????? (frontend blade)
Route::get('/my-recordings', [RecordingController::class, 'index'])->name('recordings.index');

// routes/web.php
Route::get('/user-recordings', [RecordingController::class, 'getRecordingsByUser']);

//Route::post('/upload-video', [RecordingController::class, 'uploadVideo'])->middleware('auth');
Route::post('/upload-video', [RecordingController::class, 'uploadVideo']);
Route::post('/upload-video-chunk', [RecordingController::class, 'uploadVideoChunk']);
Route::post('/finalize-video-upload', [RecordingController::class, 'finalizeVideoUpload']);
