<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
  
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

Route::middleware('auth')->group(function (){
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
});

Route::middleware('auth')->prefix('dashboard')->group(function (){
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    //    users crud
    Route::get('/users',[\App\Http\Controllers\AuthController::class,'index'])->name('dashboard.users');
    Route::get('/staffs',[\App\Http\Controllers\AuthController::class,'staff'])->name('dashboard.staff');
    Route::get('/merchant',[\App\Http\Controllers\AuthController::class,'merchant'])->name('dashboard.merchant');
    Route::get('/user_create',[\App\Http\Controllers\AuthController::class,'create'])->name('dashboard.user.create');
    Route::get('/supervisor',[\App\Http\Controllers\AuthController::class,'supervisor'])->name('dashboard.supervisor');
 
    Route::get('/user_edit/{id}',[\App\Http\Controllers\AuthController::class,'edit'])->name('dashboard.user.edit');
    Route::post('/user_store',[\App\Http\Controllers\AuthController::class,'registration'])->name('dashboard.user.store');
    Route::post('/user_update/{id}',[\App\Http\Controllers\AuthController::class,'update_user'])->name('dashboard.user.update');
    Route::get('/user_delete/{id}',[\App\Http\Controllers\AuthController::class,'delete'])->name('dashboard.user.delete');
    Route::get('/merchant/ledger/{id}',[\App\Http\Controllers\CourierRequestsController::class,'ledger'])->name('dashboard.merchant.ledger.view');

    //courier request
    Route::get('/courier_request',[\App\Http\Controllers\CourierRequestsController::class,'index'])->name('dashboard.courier.request');
    Route::get('/courier_request_create',[\App\Http\Controllers\CourierRequestsController::class,'create'])->name('dashboard.courier.create.request');
    Route::get('/courier_request_edit/{id}',[\App\Http\Controllers\CourierRequestsController::class,'edit'])->name('dashboard.courier.request.edit');
    Route::post('/courier_request_create',[\App\Http\Controllers\CourierRequestsController::class,'store'])->name('dashboard.courier.create.store.request');
    Route::get('/courier_request/{id}',[\App\Http\Controllers\CourierRequestsController::class,'show'])->name('dashboard.courier.request.info');
    Route::post('/courier_request_update/{id}',[\App\Http\Controllers\CourierRequestsController::class,'update'])->name('dashboard.courier.request.update');
    Route::get('/courier_request_delete/{id}',[\App\Http\Controllers\CourierRequestsController::class,'destroy'])->name('dashboard.courier.request.delete');
    Route::post('/courier_request_update_status/{id}', [\App\Http\Controllers\CourierRequestsController::class, 'update_status'])->name('dashboard.courier.request.update.status');
    Route::post('/courier_cod_payment_request_status/{id}', [\App\Http\Controllers\CourierRequestsController::class, 'cod_update_status'])->name('dashboard.cod.courier.request.payment.status');
    Route::post('/courier_bulk_status_change',[\App\Http\Controllers\CourierRequestsController::class, 'bulk_status'])->name('dashboard.courier.bulk.status.request.change');
    Route::get('/courier_request/{id}/print/', [\App\Http\Controllers\CourierRequestsController::class, 'printer'])->name('courier-request-printer');
    Route::get('/courier_request/hub/{id}/transfer/', [\App\Http\Controllers\CourierRequestsController::class, 'hub_transfer'])->name('courier-request-hub-transfer');
    Route::post('/courier_request/hub/store/{id}/transfer/', [\App\Http\Controllers\CourierRequestsController::class, 'hub_transfer_store'])->name('courier_request_hub_transfer_store');
    Route::get('/pickup/hub/courier_request',[\App\Http\Controllers\CourierRequestsController::class,'pickup'])->name('dashboard.pickup.courier.request');
    Route::get('/transit/hub/courier_request',[\App\Http\Controllers\CourierRequestsController::class,'transit'])->name('dashboard.transit.courier.request');
    Route::get('/delivery/hub/courier_request',[\App\Http\Controllers\CourierRequestsController::class,'delivery'])->name('dashboard.delivery.courier.request');
    Route::post('/courier/cod_payment_request/', [\App\Http\Controllers\CourierRequestsController::class, 'cod_payment_status'])->name('dashboard.status.cod.courier.request.payment.status');
    Route::get('/merchant/special/request/', [\App\Http\Controllers\AuthController::class, 'merchant_special_request'])->name('dashboard.merchant.special.request');
    
    Route::get('/merchant/yes/special/request/{id}/{sid}', [\App\Http\Controllers\AuthController::class, 'merchant_yes_special_request'])->name('dashboard.special.merchant.yes');
    Route::get('/merchant/no/special/request/{id}/{sid}', [\App\Http\Controllers\AuthController::class, 'merchant_yes_special_request'])->name('dashboard.special.merchant.no');
    
    Route::get('/remaining',[\App\Http\Controllers\CourierRequestsController::class,'remaining'])->name('dashboard.remaining');
    Route::get('/completed',[\App\Http\Controllers\CourierRequestsController::class,'completed'])->name('dashboard.completed');
    Route::get('/delivered',[\App\Http\Controllers\CourierRequestsController::class,'delivered'])->name('dashboard.delivered');
    Route::get('/returned',[\App\Http\Controllers\CourierRequestsController::class,'returned'])->name('dashboard.returned');
    Route::get('/cancelled',[\App\Http\Controllers\CourierRequestsController::class,'cancelled'])->name('dashboard.cancelled');
    Route::get('/hold',[\App\Http\Controllers\CourierRequestsController::class,'hold'])->name('dashboard.hold');
    Route::get('/daily/accounts',[\App\Http\Controllers\CourierRequestsController::class,'daily_accounts'])->name('dashboard.daily.accounts');
    Route::get('search/daily/accounts',[\App\Http\Controllers\CourierRequestsController::class,'search'])->name('dashboard.search.daily.accounts');
    Route::get('/export_excel/excel', [\App\Http\Controllers\CourierRequestsController::class,'excel'])->name('export_excel.excel');
    
    Route::get('/daily/delivery/accounts',[\App\Http\Controllers\CourierRequestsController::class,'daily_delivery_accounts'])->name('dashboard.daily.delivery');
    Route::get('search/delivery/daily/accounts',[\App\Http\Controllers\CourierRequestsController::class,'deliverysearch'])->name('dashboard.search.delivery_hub.daily.accounts');
    Route::get('/daily/transactions',[\App\Http\Controllers\CourierRequestsController::class,'daily_transactions'])->name('dashboard.daily.transactions');
    Route::get('search/transactions/daily/accounts',[\App\Http\Controllers\CourierRequestsController::class,'transactionssearch'])->name('dashboard.search.daily_transactions.accounts');
    
    Route::get('/daily/hub/payment',[\App\Http\Controllers\CourierRequestsController::class,'daily_hubpayment_accounts'])->name('dashboard.hub.payment.account');
    Route::get('search/hub/daily/accounts',[\App\Http\Controllers\CourierRequestsController::class,'hubpaymentsearch'])->name('dashboard.search.daily_hub_payment.accounts');
    
    Route::get('merchant/due/lists/{id}',[\App\Http\Controllers\CourierRequestsController::class,'merchantduelist'])->name('dashboard.merchant.due.list');
     Route::get('hub/due/lists/show/{id}',[\App\Http\Controllers\CourierRequestsController::class,'hubduelist'])->name('dashboard.account.hub.list.show');
     
     Route::get('/daily/bkash/transactions',[\App\Http\Controllers\CourierRequestsController::class,'daily_bkash_transactions'])->name('dashboard.bkash.daily.transactions');
    Route::get('/daily/search/bkash/transactions',[\App\Http\Controllers\CourierRequestsController::class,'search_bkash_transactions'])->name('dashboard.search.bkash.daily_bkash_transactions.accounts');
    Route::get('/daily/nagad/transactions',[\App\Http\Controllers\CourierRequestsController::class,'daily_nagad_transactions'])->name('dashboard.nagad.daily.transactions');
    Route::get('/daily/search/nagad/transactions',[\App\Http\Controllers\CourierRequestsController::class,'search_nagad_transactions'])->name('dashboard.search.nagad.daily_nagad_transactions.accounts');
    Route::get('/daily/rocket/transactions',[\App\Http\Controllers\CourierRequestsController::class,'daily_rocket_transactions'])->name('dashboard.rocket.daily.transactions');
    Route::get('/daily/search/rocket/transactions',[\App\Http\Controllers\CourierRequestsController::class,'search_rocket_transactions'])->name('dashboard.search.rocket.daily_rocket_transactions.accounts');
    Route::get('/daily/bank/transactions',[\App\Http\Controllers\CourierRequestsController::class,'daily_bank_transactions'])->name('dashboard.bank.daily.transactions');
    Route::get('/daily/search/bank/transactions',[\App\Http\Controllers\CourierRequestsController::class,'search_bank_transactions'])->name('dashboard.search.bank.daily_bank_transactions.accounts');


    //courier bulk request
    Route::get('/courier_bulk_request',[\App\Http\Controllers\BulkrequestsController::class,'index'])->name('dashboard.courier.bulk.request');
    Route::get('/courier_bulk_request_create',[\App\Http\Controllers\BulkrequestsController::class,'create'])->name('dashboard.courier.bulk.request.create');
    Route::get('/courier_bulk_request_edit/{id}',[\App\Http\Controllers\BulkrequestsController::class,'edit'])->name('dashboard.courier.bulk.request.edit');
    Route::post('/courier_bulk_request_create',[\App\Http\Controllers\BulkrequestsController::class,'store']);
    Route::get('/courier_bulk_request/{id}',[\App\Http\Controllers\BulkrequestsController::class,'show'])->name('dashboard.courier.bulk.request.info');
    Route::post('/courier_bulk_request_update/{id}',[\App\Http\Controllers\BulkrequestsController::class,'update']);
    Route::get('/courier_bulk_request_delete/{id}',[\App\Http\Controllers\BulkrequestsController::class,'destroy'])->name('dashboard.courier.bulk.request.delete');
    Route::post('/courier_bulk_request_update_status/{id}', [\App\Http\Controllers\BulkrequestsController::class, 'update_status']);
    Route::post('/courier_bulk_request_upload',[\App\Http\Controllers\BulkrequestsController::class, 'upload'])->name('dashboard.courier.bulk.request.upload');


    //buy4u request
    Route::get('/buy4u_request',[\App\Http\Controllers\Buy4uRequestController::class,'index'])->name('dashboard.buy4u.request');
    Route::get('/buy4u_request_create',[\App\Http\Controllers\Buy4uRequestController::class,'create'])->name('dashboard.buy4u.create');
    Route::post('/buy4u_request_create',[\App\Http\Controllers\Buy4uRequestController::class,'store'])->name('dashboard.buy4u.store');
    Route::get('/buy4u_request/{id}',[\App\Http\Controllers\Buy4uRequestController::class,'show'])->name('dashboard.buy4u.show');
    Route::get('/buy4u_request_edit/{id}',[\App\Http\Controllers\Buy4uRequestController::class,'edit'])->name('dashboard.buy4u.edit');
    Route::post('/buy4u_request_update/{id}',[\App\Http\Controllers\Buy4uRequestController::class,'update'])->name('dashboard.buy4u.request.update');
    Route::get('/buy4u_request_delete/{id}',[\App\Http\Controllers\Buy4uRequestController::class,'destroy'])->name('dashboard.buy4u.request.delete');;
    Route::post('/buy4u_request_update_status/{id}', [\App\Http\Controllers\Buy4uRequestController::class, 'update_status'])->name('dashboard.buy4u.update.status');

    //courier types
    Route::get('/courier_type',[\App\Http\Controllers\CourierTypesController::class,'index'])->name('dashboard.courier.type');
    Route::get('/courier_type_create',[\App\Http\Controllers\CourierTypesController::class,'create'])->name('dashboard.courier.type.create');
    Route::post('/courier_type_create',[\App\Http\Controllers\CourierTypesController::class,'store'])->name('dashboard.courier.type.store.create');

    Route::get('/courier_type_edit/{id}',[\App\Http\Controllers\CourierTypesController::class,'edit'])->name('dashboard.courier.type.edit');
    Route::post('/courier_type_update/{id}',[\App\Http\Controllers\CourierTypesController::class,'update'])->name('dashboard.courier.type.update');
    Route::get('/courier_type_delete/{id}',[\App\Http\Controllers\CourierTypesController::class,'destroy'])->name('dashboard.courier.courier.type.delete');

    //buy4u types
    Route::get('/buy4u_type',[\App\Http\Controllers\Buy4uTypesController::class,'index'])->name('dashboard.buy4u.type');
    Route::get('/buy4u_type_create',[\App\Http\Controllers\Buy4uTypesController::class,'create'])->name('dashboard.buy4u.type.create');
    Route::get('/buy4u_type_edit/{id}',[\App\Http\Controllers\Buy4uTypesController::class,'edit'])->name('dashboard.buy4u.type.edit');
    Route::post('/buy4u_type_update/{id}',[\App\Http\Controllers\Buy4uTypesController::class,'update'])->name('dashboard.buy4u.type.update');
    Route::post('/buy4u_type_create',[\App\Http\Controllers\Buy4uTypesController::class,'store'])->name('dashboard.buy4u.type.store.create');
    Route::get('/buy4u_type_delete/{id}',[\App\Http\Controllers\Buy4uTypesController::class,'destroy'])->name('dashboard.buy4u.type.delete');

    //delivery mode
    Route::get('/delivery_mode',[\App\Http\Controllers\DeliveryModesController::class,'index'])->name('dashboard.delivery.mode');
    Route::get('/delivery_mode_create',[\App\Http\Controllers\DeliveryModesController::class,'create'])->name('dashboard.delivery.mode.create');
    Route::post('/delivery_mode_create',[\App\Http\Controllers\DeliveryModesController::class,'store'])->name('dashboard.delivery.mode.store.create');
    Route::get('/delivery_mode_edit/{id}',[\App\Http\Controllers\DeliveryModesController::class,'edit'])->name('dashboard.delivery.mode.edit');
    Route::post('/delivery_mode_update/{id}',[\App\Http\Controllers\DeliveryModesController::class,'update'])->name('dashboard.delivery.mode.update');
    Route::get('/delivery_mode_delete/{id}',[\App\Http\Controllers\DeliveryModesController::class,'destroy'])->name('dashboard.delivery.mode.delete');

    //Unit types
    Route::get('/unit_type',[\App\Http\Controllers\UnitTypeController::class,'index'])->name('dashboard.unit.type');
    Route::get('/unit_type_create',[\App\Http\Controllers\UnitTypeController::class,'create'])->name('dashboard.unit.type.create');
    Route::post('/unit_type_create',[\App\Http\Controllers\UnitTypeController::class,'store'])->name('dashboard.unit.type.store.create');
    Route::get('/unit_type_edit/{id}',[\App\Http\Controllers\UnitTypeController::class,'edit'])->name('dashboard.unit.type.edit');
    Route::post('/unit_type_update/{id}',[\App\Http\Controllers\UnitTypeController::class,'update'])->name('dashboard.unit.type.update');
    Route::get('/unit_type_delete/{id}',[\App\Http\Controllers\UnitTypeController::class,'destroy'])->name('dashboard.unit.type.delete');

    //Package types
    Route::get('/package_type',[\App\Http\Controllers\PackagingTypesController::class,'index'])->name('dashboard.package.type');
    Route::get('/package_type_create',[\App\Http\Controllers\PackagingTypesController::class,'create'])->name('dashboard.package.type.create');
    Route::post('/package_type_create',[\App\Http\Controllers\PackagingTypesController::class,'store'])->name('dashboard.package.type.store.create');
    Route::get('/package_type_edit/{id}',[\App\Http\Controllers\PackagingTypesController::class,'edit'])->name('dashboard.package.type.edit');
    Route::post('/package_type_update/{id}',[\App\Http\Controllers\PackagingTypesController::class,'update'])->name('dashboard.package.type.update');
    Route::get('/package_type_delete/{id}',[\App\Http\Controllers\PackagingTypesController::class,'destroy'])->name('dashboard.package.type.delete');

    //Status
    Route::get('/status',[\App\Http\Controllers\StatusController::class,'index'])->name('dashboard.status');
    Route::get('/status_create',[\App\Http\Controllers\StatusController::class,'create'])->name('dashboard.status.create');
    Route::post('/status_create',[\App\Http\Controllers\StatusController::class,'store'])->name('dashboard.status.store.create');
    Route::get('/status_edit/{id}',[\App\Http\Controllers\StatusController::class,'edit'])->name('dashboard.status.edit');
    Route::post('/status_update/{id}',[\App\Http\Controllers\StatusController::class,'update'])->name('dashboard.status.update');
    Route::get('/status_delete/{id}',[\App\Http\Controllers\StatusController::class,'destroy'])->name('dashboard.status.delete');

    //city
    Route::get('/city',[\App\Http\Controllers\CitiesController::class,'index'])->name('dashboard.city');
    Route::get('/city_create',[\App\Http\Controllers\CitiesController::class,'create'])->name('dashboard.city.create');
    Route::post('/city_create',[\App\Http\Controllers\CitiesController::class,'store'])->name('dashboard.city.store.create');
    Route::get('/city_edit/{id}',[\App\Http\Controllers\CitiesController::class,'edit'])->name('dashboard.city.edit');
    Route::post('/city_update/{id}',[\App\Http\Controllers\CitiesController::class,'update'])->name('dashboard.city.update');
    Route::get('/city_delete/{id}',[\App\Http\Controllers\CitiesController::class,'destroy'])->name('dashboard.city.delete');

    //area
    Route::get('/area',[\App\Http\Controllers\AreasController::class,'index'])->name('dashboard.area');
    Route::get('/area_create',[\App\Http\Controllers\AreasController::class,'create'])->name('dashboard.area.create');
    Route::post('/area_create',[\App\Http\Controllers\AreasController::class,'store'])->name('dashboard.area.store.create');
    Route::get('/area_edit/{id}',[\App\Http\Controllers\AreasController::class,'edit'])->name('dashboard.area.edit');
    Route::post('/area_update/{id}',[\App\Http\Controllers\AreasController::class,'update'])->name('dashboard.area.update');
    Route::get('/area_delete/{id}',[\App\Http\Controllers\AreasController::class,'destroy'])->name('dashboard.area.delete');

    //branch
    Route::get('/branch',[\App\Http\Controllers\BranchesController::class,'index'])->name('dashboard.branch');
    Route::get('/branch_create',[\App\Http\Controllers\BranchesController::class,'create'])->name('dashboard.branch.create');
    Route::post('/branch_create',[\App\Http\Controllers\BranchesController::class,'store'])->name('dashboard.branch.store.create');
    Route::post('/branch_creates',[\App\Http\Controllers\BranchesController::class,'stores'])->name('branch.dashboard.store.create');

    Route::get('/branch_edit/{id}',[\App\Http\Controllers\BranchesController::class,'edit'])->name('dashboard.branch.edit');
    Route::post('/branch_update/{id}',[\App\Http\Controllers\BranchesController::class,'update'])->name('dashboard.branch.update');
    Route::post('/branch_updates/{id}',[\App\Http\Controllers\BranchesController::class,'updates'])->name('branch.dashboard.update');
    Route::get('/branch_delete/{id}',[\App\Http\Controllers\BranchesController::class,'destroy'])->name('dashboard.branch.delete');
    
    //branch
    Route::get('/accounts',[\App\Http\Controllers\BranchesController::class,'accounts'])->name('dashboard.accounts');
    Route::get('/payments/receive',[\App\Http\Controllers\BranchesController::class,'paymentReceive'])->name('dashboard.payment.receive');
    Route::get('/hub/payments/paid/{id}',[\App\Http\Controllers\BranchesController::class,'paymentHubList'])->name('dashboard.account.paid.hub.lists.show');

    //pricing
    Route::get('/pricing',[\App\Http\Controllers\PricingsController::class,'index'])->name('dashboard.pricing');
    Route::get('/pricing_create',[\App\Http\Controllers\PricingsController::class,'create'])->name('dashboard.pricing.create');
    Route::post('/pricing_create',[\App\Http\Controllers\PricingsController::class,'store'])->name('dashboard.pricing.store.create');
    Route::get('/pricing_edit/{id}',[\App\Http\Controllers\PricingsController::class,'edit'])->name('dashboard.pricing.edit');
    Route::post('/pricing_update/{id}',[\App\Http\Controllers\PricingsController::class,'update'])->name('dashboard.pricing.update');
    Route::get('/pricing_delete/{id}',[\App\Http\Controllers\PricingsController::class,'destroy'])->name('dashboard.pricing.delete');

    //sliders collection
    Route::get('/sliders_collection',[\App\Http\Controllers\SliderCollectionsController::class,'index'])->name('dashboard.sliders.collection');
    Route::get('/sliders_collection_create',[\App\Http\Controllers\SliderCollectionsController::class,'create'])->name('dashboard.sliders.collection.create');
    Route::post('/sliders_collection_create',[\App\Http\Controllers\SliderCollectionsController::class,'store'])->name('dashboard.sliders.collection.store.create');
    Route::get('/sliders_collection_edit/{id}',[\App\Http\Controllers\SliderCollectionsController::class,'edit'])->name('dashboard.sliders.collection.edit');
    Route::post('/sliders_collection_update/{id}',[\App\Http\Controllers\SliderCollectionsController::class,'update'])->name('dashboard.sliders.collection.update');
    Route::get('/sliders_collection_delete/{id}',[\App\Http\Controllers\SliderCollectionsController::class,'destroy'])->name('dashboard.sliders.collection.delete');

    //sliders
    Route::get('/slider',[\App\Http\Controllers\SlidersController::class,'index'])->name('dashboard.slider');
    Route::get('/slider_create',[\App\Http\Controllers\SlidersController::class,'create'])->name('dashboard.slider.create');
    Route::post('/slider_create',[\App\Http\Controllers\SlidersController::class,'store'])->name('dashboard.slider.store.create');
    Route::get('/slider_edit/{id}',[\App\Http\Controllers\SlidersController::class,'edit'])->name('dashboard.slider.edit');
    Route::post('/slider_update/{id}',[\App\Http\Controllers\SlidersController::class,'update'])->name('dashboard.slider.update');
    Route::get('/slider_delete/{id}',[\App\Http\Controllers\SlidersController::class,'destroy'])->name('dashboard.slider.delete');

    Route::post('/get_area_by_city',[\App\Http\Controllers\BranchesController::class,'getarea'])->name('area.get_area_by_city');
    Route::post('/get_hub_by_area',[\App\Http\Controllers\BranchesController::class,'gethub'])->name('hub.get_hub_by_area');
    Route::post('/get_hub_by_city',[\App\Http\Controllers\BranchesController::class,'gethubbycity'])->name('hub.get_hub_by_city');
    Route::post('/get_area_by_hub',[\App\Http\Controllers\BranchesController::class,'getareabyhub'])->name('area.get_area_by_hub');
    
    Route::post('/get_senderarea_by_sendercity',[\App\Http\Controllers\BranchesController::class,'getsenderarea'])->name('area.get_senderarea_by_sendercity');
    Route::post('/get_receiverarea_by_receivercity',[\App\Http\Controllers\BranchesController::class,'getreceiverarea'])->name('area.get_receiverarea_by_receivercity');
    Route::post('/get_hub_using_area',[\App\Http\Controllers\BranchesController::class,'getareahub'])->name('area.get_hub_by_area');
    
    Route::post('/get_delivermode_by_city',[\App\Http\Controllers\BranchesController::class,'getdelivermode'])->name('deliver.get_delivermode_by_city');
    Route::post('/get_deliverytype_by_deliverymode',[\App\Http\Controllers\BranchesController::class,'getdeliverytype'])->name('deliver.get_deliverytype_by_deliverymode');
    Route::post('/get_weight_by_all',[\App\Http\Controllers\BranchesController::class,'getweight'])->name('weight.get_weight_by_all');
    Route::post('/get_price_by_weight',[\App\Http\Controllers\BranchesController::class,'getpricebyweight'])->name('price.get_price_by_weight');
    
    Route::get('/bkash/merchant/payable',[\App\Http\Controllers\CourierRequestsController::class,'bkash_merchant_payable'])->name('dashboard.bkash.merchant.payable');
    Route::get('/nagad/merchant/payable',[\App\Http\Controllers\CourierRequestsController::class,'nagad_merchant_payable'])->name('dashboard.nagad.merchant.payable');
    Route::get('/rocket/merchant/payable',[\App\Http\Controllers\CourierRequestsController::class,'rocket_merchant_payable'])->name('dashboard.rocket.merchant.payable');
    Route::get('/bank/merchant/payable',[\App\Http\Controllers\CourierRequestsController::class,'bank_merchant_payable'])->name('dashboard.bank.merchant.payable');
    Route::get('/all/merchant/payable',[\App\Http\Controllers\CourierRequestsController::class,'all_merchant_payable'])->name('dashboard.all.merchant.payable');
    Route::get('/bkash/agent/pay/list',[\App\Http\Controllers\CourierRequestsController::class,'bkash_agent_to_merchant_payable'])->name('dashboard.bkash.agent.pay.list'); 
    
    Route::get('/password', [DashboardController::class, 'password'])->name('dashboard.password');
    Route::post('/passwords/changing', [DashboardController::class, 'passwordchange'])->name('dashboard.password.changing');
    
    /*support*/
    Route::get('/support',[\App\Http\Controllers\IssueController::class,'index'])->name('dashboard.support');
    Route::get('/support/create',[\App\Http\Controllers\IssueController::class,'create'])->name('dashboard.support.create');
    Route::post('/support/store',[\App\Http\Controllers\IssueController::class,'store'])->name('dashboard.support.store');
    Route::get('/support/details/{id}',[\App\Http\Controllers\IssueController::class,'show'])->name('dashboard.support.list.show');
    Route::post('/support/ticket/lists/{id}',[\App\Http\Controllers\IssueController::class,'list_store'])->name('dashboard.support.ticket.list.add');
    
    /*Invoice*/
    Route::get('/invoice/generator',[\App\Http\Controllers\CourierRequestsController::class,'invoice'])->name('dashboard.invoice');
    Route::post('/mail/invoice/generator',[\App\Http\Controllers\CourierRequestsController::class,'invoice_mail'])->name('dashboard.invoice.genrate.mail');
    Route::get('/invoice/lists/generator',[\App\Http\Controllers\CourierRequestsController::class,'invoice_list'])->name('dashboard.list.invoice');
    Route::get('/invoice/show/details/{id}',[\App\Http\Controllers\CourierRequestsController::class,'show_invoice'])->name('dashboard.invoice.show.list');
    
    Route::get('/invoice/email/send/{id}',[\App\Http\Controllers\CourierRequestsController::class,'email_invoice'])->name('dashboard.invoice.email.send');
    
    Route::get('/pickup/rider/courier/{id}',[\App\Http\Controllers\CourierRequestsController::class,'pickup_rider_courier'])->name('dashboard.pickup.rider.list');
    Route::post('/pickup/rider/payment/courier',[\App\Http\Controllers\CourierRequestsController::class,'pickup_rider_payment_courier'])->name('dashboard.courier.rider.payment.request');
    
    Route::get('/delivery/rider/courier/{id}',[\App\Http\Controllers\CourierRequestsController::class,'delivery_rider_courier'])->name('dashboard.delivery.rider.list');
    Route::post('/delivery/rider/payment/courier',[\App\Http\Controllers\CourierRequestsController::class,'delivery_rider_payment_courier'])->name('dashboard.courier.rider.delivery.payment.request');
    
    Route::get('/pickup/agent/courier/{id}',[\App\Http\Controllers\CourierRequestsController::class,'pickup_agent_courier'])->name('dashboard.pickup.agent.list');
    Route::post('/pickup/agent/payment/courier',[\App\Http\Controllers\CourierRequestsController::class,'pickup_agent_payment_courier'])->name('dashboard.courier.agent.payment.request');
    
    Route::get('/delivery/agent/courier/{id}',[\App\Http\Controllers\CourierRequestsController::class,'delivery_agent_courier'])->name('dashboard.delivery.agent.list');
    Route::post('/delivery/agent/payment/courier',[\App\Http\Controllers\CourierRequestsController::class,'delivery_agent_payment_courier'])->name('dashboard.courier.agent.delivery.payment.request');
    
    Route::get('/form/entry',[\App\Http\Controllers\DashboardController::class,'dashboard_store']);
    Route::post('/form/update/entry/{id}',[\App\Http\Controllers\DashboardController::class,'dashboard_update'])->name('dashboard.form.entry.update');

    
    Route::get('logouts', function (){
        auth()->logout();
        Session()->flush();
        return Redirect::to('/');
    })->name('logouts');
    

});