<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlidersController;
use App\Http\Controllers\SliderCollectionsController;
use App\Http\Controllers\CourierTypesController;
use App\Http\Controllers\Buy4uTypesController;
use App\Http\Controllers\DeliveryModesController;
use App\Http\Controllers\PackagingTypesController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PricingsController;
use App\Http\Controllers\CourierRequestsController;
use App\Http\Controllers\Buy4uRequestController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\DashboardController;
 
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/customer-register', [AuthController::class, 'cusRegister']);
Route::post('/custom-login', [AuthController::class, 'login']);
Route::post('/custom-register', [AuthController::class, 'register']);
Route::get('/privacy-policy', [DashboardController::class, 'privacypolicy']);
Route::get('/courier_requests/parcel-tracker/{id}', [CourierRequestsController::class, 'tracker']);
Route::post('/courier_requests/verfication/{id}', [CourierRequestsController::class, 'verfication']);
Route::post('/courier_requests/update_otp_status', [CourierRequestsController::class, 'update_otp_status']);
Route::get('/cities', [CitiesController::class, 'index']);
Route::get('/cities/{slug}/areas', [CitiesController::class, 'areas']);
Route::post('/device_id/{id}', [AuthController::class, 'device']);

Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::get('/validate-token', [AuthController::class, 'validate_token']);
    Route::get('/custom-logout', [AuthController::class, 'logout']);
    Route::post('/passowrd-change', [AuthController::class, 'change_password']);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'update_profile']);
    
   // Supervisior
    Route::prefix('/supervisior')->group(function(){
        Route::get('/pickup/requests', [CourierRequestsController::class, 'pickup_request']);
        Route::get('/delivery/requests', [CourierRequestsController::class, 'delivery_request']);
        Route::get('/hub/transfer/{id}', [CourierRequestsController::class, 'hub_transfers_id']);
        Route::post('/hub/transfer/store/{id}', [CourierRequestsController::class, 'hub_transfers_id_store']);
        Route::post('/bulk/hub/transfer/store', [CourierRequestsController::class, 'bulk_hub_transfers_id_store']);
        Route::get('/hub/rider', [CourierRequestsController::class, 'hub_rider']);
        Route::get('/hub/states', [CourierRequestsController::class, 'states']);
        Route::post('/hub/riders/assign', [CourierRequestsController::class, 'hub_rider_Assign']);
    });
    
    //Bkash Agent
    Route::prefix('/bkash_agent')->group(function(){
        Route::get('/courier/requests', [CourierRequestsController::class, 'agent_request']);
        Route::post('/sent/courier/requests/payment', [CourierRequestsController::class, 'agent_sent_payment_request']);
        Route::get('/agent/paid/list', [CourierRequestsController::class, 'agent_paid_list']);
    });
    
    // Support
    Route::prefix('/supports')->group(function(){
        Route::get('/', [\App\Http\Controllers\IssueController::class,'supports_list']);
        Route::post('/store', [\App\Http\Controllers\IssueController::class,'supports_store']);
        Route::get('/details/{id}', [\App\Http\Controllers\IssueController::class,'supports_details']);
        Route::post('/details/store/{id}', [\App\Http\Controllers\IssueController::class,'supports_details_store']);
    });

    // Slider
    Route::prefix('/sliders')->group(function(){
        Route::post('/', [SlidersController::class, 'store']);
        Route::get('/', [SlidersController::class, 'index']);
        Route::delete('/delete/{id}', [SlidersController::class, 'destroy']);
    });

// Slider Collection
    Route::prefix('/slider_collections')->group(function(){
        Route::post('/', [SliderCollectionsController::class, 'store']);
        Route::get('/', [SliderCollectionsController::class, 'index']);
        Route::delete('/delete/{id}', [SliderCollectionsController::class, 'destroy']);
        Route::get('/show/{slug}', [SliderCollectionsController::class, 'show']);
    });

// Courier Type
    Route::prefix('/courier_types')->group(function(){
        Route::post('/', [CourierTypesController::class, 'store']);
        Route::get('/', [CourierTypesController::class, 'index']);
        Route::delete('/delete/{id}', [CourierTypesController::class, 'destroy']);
        Route::get('/show/{slug}', [CourierTypesController::class, 'show']);
        Route::get('/{slug}/delivery_modes', [CourierTypesController::class, 'delivery_modes']);
    });

    // Buy4u Type
    Route::prefix('/buy4u_types')->group(function(){
        Route::post('/', [Buy4uTypesController::class, 'store']);
        Route::get('/', [Buy4uTypesController::class, 'index']);
        Route::delete('/delete/{id}', [Buy4uTypesController::class, 'destroy']);
        Route::get('/show/{id}', [Buy4uTypesController::class, 'show']);
        Route::get('/{slug}/delivery_modes', [Buy4uTypesController::class, 'delivery_modes']);
    });

    // Unit Type
    Route::prefix('/unit_types')->group(function(){
        Route::post('/', [UnitTypeController::class, 'store']);
        Route::get('/', [UnitTypeController::class, 'index']);
        Route::delete('/delete/{id}', [UnitTypeController::class, 'destroy']);
        Route::get('/show/{id}', [UnitTypeController::class, 'show']);
    });

    // Status
    Route::prefix('/status')->group(function(){
        Route::post('/', [StatusController::class, 'store']);
        Route::get('/', [StatusController::class, 'index']);
        Route::delete('/delete/{id}', [StatusController::class, 'destroy']);
        Route::get('/show/{id}', [StatusController::class, 'show']);
    });

// Packaging Type
    Route::prefix('/packaging_types')->group(function(){
        Route::post('/', [PackagingTypesController::class, 'store']);
        Route::get('/', [PackagingTypesController::class, 'index']);
        Route::delete('/delete/{id}', [PackagingTypesController::class, 'destroy']);
        Route::get('/show/{slug}', [PackagingTypesController::class, 'show']);
    });

    // Branch
    Route::prefix('/branch')->group(function(){
        Route::post('/', [BranchesController::class, 'store']);
        Route::get('/', [BranchesController::class, 'index']);
        Route::delete('/delete/{id}', [BranchesController::class, 'destroy']);
        Route::get('/show/{slug}', [BranchesController::class, 'show']);
    });

// City
    Route::prefix('/cities')->group(function(){
        Route::post('/', [CitiesController::class, 'store']);
        /*Route::get('/', [CitiesController::class, 'index']);*/
       /* Route::get('/{slug}/areas', [CitiesController::class, 'areas']);*/
        Route::delete('/delete/{id}', [CitiesController::class, 'destroy']);
        Route::get('/show/{slug}', [CitiesController::class, 'show']);
    });

// Area
    Route::prefix('/areas')->group(function(){
        Route::post('/', [AreasController::class, 'store']);
        Route::get('/', [AreasController::class, 'index']);
        Route::delete('/delete/{id}', [AreasController::class, 'destroy']);
        Route::get('/show/{slug}', [AreasController::class, 'show']);
    });

// Delivery Mode
    Route::prefix('/delivery_modes')->group(function(){
        Route::post('/', [DeliveryModesController::class, 'store']);
        Route::get('/', [DeliveryModesController::class, 'index']);
        Route::put('/{id}', [DeliveryModesController::class, 'update']);
        Route::delete('/delete/{id}', [DeliveryModesController::class, 'destroy']);
        Route::get('/show/{slug}', [DeliveryModesController::class, 'show']);
    });

    // Pricing
    Route::prefix('/pricings')->group(function(){
        Route::post('/', [PricingsController::class, 'store']);
        Route::get('/', [PricingsController::class, 'index']);
        Route::put('/{id}', [PricingsController::class, 'update']);
        Route::delete('/delete/{id}', [PricingsController::class, 'destroy']);
        Route::get('/show/{slug}', [PricingsController::class, 'show']);
    });

    // Courier Request
    Route::prefix('/courier_requests')->group(function(){
        Route::post('/', [CourierRequestsController::class, 'store_api']);
        Route::post('/get_pricing', [CourierRequestsController::class, 'get_pricing']);
        Route::get('/', [CourierRequestsController::class, 'index']);
        Route::put('/{id}', [CourierRequestsController::class, 'update']);
        Route::delete('/delete/{id}', [CourierRequestsController::class, 'destroy']);
        Route::get('/show/{slug}', [CourierRequestsController::class, 'show']);
        Route::post('/update_status/{id}', [CourierRequestsController::class, 'update_status']);
        Route::post('/verfication/{id}', [CourierRequestsController::class, 'verfication']);
        Route::post('/status_change', [CourierRequestsController::class, 'status_change']);
        Route::get('/pickup', [CourierRequestsController::class, 'rider_pickup']);
        Route::get('/delivery', [CourierRequestsController::class, 'rider_delivery']);
        Route::post('/rider_status_change', [CourierRequestsController::class, 'rider_status_change']);
        
        Route::get('/rider/commission', [CourierRequestsController::class, 'rider_commission']);
        Route::get('/agent/commission', [CourierRequestsController::class, 'agent_commission']);
    });

    //courier bulk request upload
    Route::prefix('/courier_bulk_requests')->group(function() {
        Route::post('/upload', [\App\Http\Controllers\BulkrequestsController::class, 'upload']);
    });

    // Buy4u Request
    Route::prefix('/buy4u_request')->group(function(){
        Route::post('/', [Buy4uRequestController::class, 'store_api']);
        Route::post('/get_pricing', [Buy4uRequestController::class, 'get_pricing']);
        Route::get('/', [Buy4uRequestController::class, 'index']);
        Route::put('/{id}', [Buy4uRequestController::class, 'update']);
        Route::delete('/delete/{id}', [Buy4uRequestController::class, 'destroy']);
        Route::get('/show/{slug}', [Buy4uRequestController::class, 'show']);
        Route::post('/update_status/{id}', [Buy4uRequestController::class, 'update_status']);
    });
});
