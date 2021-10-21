<?php

namespace App\Http\Controllers;

use App\Models\CourierRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;  
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Dashboard;

class DashboardController extends Model 
{   
    use HasFactory;
    public function dashboard(Request $request){
       $data['dashboard'] = \DB::table('dashboards')->first();
        return view('dashboard',$data);  
    }

    public function privacypolicy(){
        return view('privacypolicy');
    }
    public function password(){
        return view('password');
    }

    public function passwordchange(Request $request){
        $validatedData = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $users_info = $request->except('_token','password_confirmation');
        $users_info['password'] = bcrypt($request->password); 
        User::where('id', Auth::user()->id)->update($users_info);
        session()->flash('message','Password Updated Successfully');
        return redirect(route('dashboard.password'));
    }
    
    public function dashboard_store(){
        $data['dashboard'] = \DB::table('dashboards')->first();
        return view('dashboard_form',$data);
    }

    // public function dashboard_update(Request $request,$id){
    //     $update = $request->except('_token');
    //     \DB::table('dashboard')->where('id',$id)->update($update);
    //     return redirect()->back()->with('message','Update Successful');
    // }
    public function dashboard_update(Request $request,$id){
        $update = $request->except('_token');

        $dashboardForm = Dashboard::findOrFail($id);

        // dd($dashboardForm);

        $todays_percel_entry_now = $request->todays_parcel_entry - $dashboardForm->todays_parcel_entry;
        $todays_percel_cancel_now = $request->todays_cancel_parcel - $dashboardForm->todays_cancel_parcel;
        $deliveryChargeEnteredToday = 0;
        $DELIVERY_CHARGE_COLLECTED_TODAY = 0;
        $deli_crge_cl_today = 0;
        $diff1 = 0;
        $diff2 = 0;
        if ($request->has('todays_parcel_entry')) {
            $dashboardForm->todays_parcel_entry = $request->todays_parcel_entry;
        }
        if ($request->has('todays_cancel_parcel')) {
            $dashboardForm->todays_cancel_parcel = $request->todays_cancel_parcel;
        }
        if ($request->has('total_parcel_entry_till_now')) {
            $dashboardForm->total_parcel_entry_till_now = $dashboardForm->total_parcel_entry_till_now + $todays_percel_entry_now;
        }
        if ($request->has('total_cancel_parcel_till_now')) {
            $dashboardForm->total_cancel_parcel_till_now = $dashboardForm->total_cancel_parcel_till_now + $todays_percel_cancel_now;
        }
        if ($request->has('total_delivered_today')) {
            $dashboardForm->total_delivered_today = $todays_percel_entry_now;
        }
        if ($request->has('total_delivered_till_now')) {
            $dashboardForm->total_delivered_till_now = $dashboardForm->total_delivered_till_now + $todays_percel_entry_now;
        }
        if ($request->has('delivery_charge_entered_today')) {
            $deliveryChargeEnteredToday = $request->todays_parcel_entry * 55;
            $dashboardForm->delivery_charge_entered_today = $deliveryChargeEnteredToday;
            
        }
        if ($request->has('delivery_charge_total_receivable')) {
            $DELIVERY_CHARGE_TOTAL_RECEIVABLE = $todays_percel_entry_now * 55;
            $dashboardForm->delivery_charge_total_receivable = $dashboardForm->delivery_charge_total_receivable + $DELIVERY_CHARGE_TOTAL_RECEIVABLE;
        }
        if ($request->has('delivery_charge_collected_today')) {
            
            $DELIVERY_CHARGE_COLLECTED_TODAY = ($deliveryChargeEnteredToday * 90) / 100;
            $dashboardForm->delivery_charge_collected_today = $DELIVERY_CHARGE_COLLECTED_TODAY;
            $deli_crge_cl_today = $DELIVERY_CHARGE_COLLECTED_TODAY - $dashboardForm->delivery_charge_collected_today;
        }
        if ($request->has('delivery_charge_due_today')) {
            $dashboardForm->delivery_charge_due_today = $deliveryChargeEnteredToday - $DELIVERY_CHARGE_COLLECTED_TODAY;
        }
        if ($request->has('delivery_charge_collected_till_now')) {
            $dashboardForm->delivery_charge_collected_till_now = $dashboardForm->delivery_charge_collected_till_now + $deli_crge_cl_today;
        }
        if ($request->has('cod_entry_today')) {
            $dashboardForm->cod_entry_today = $request->todays_parcel_entry * 1393;
        }
        if ($request->has('cod_collected_receivable_by_merchant')) {
            $dashboardForm->cod_collected_receivable_by_merchant = $dashboardForm->cod_collected_receivable_by_merchant + ($todays_percel_entry_now * 1157);
            $diff1 = $dashboardForm->cod_collected_receivable_by_merchant + ($todays_percel_entry_now * 1157);
        }
        if ($request->has('total_cod_paid_to_merchant')) {
            $dashboardForm->total_cod_paid_to_merchant = $dashboardForm->total_cod_paid_to_merchant + ($todays_percel_entry_now * 843);
            $diff2 = $dashboardForm->total_cod_paid_to_merchant + ($todays_percel_entry_now * 843);
        }
        if ($request->has('total_cod_due')) {
            $dashboardForm->total_cod_due = $diff1 - $diff2;
        }
        if ($request->has('cod_collected_till_now')) {
            $dashboardForm->cod_collected_till_now = $dashboardForm->cod_collected_till_now + $diff1;
        }

        $dashboardForm->save();

        //$dashboardForm->update($update);

        return redirect()->back()->with('message','Update Successful');
    }
}