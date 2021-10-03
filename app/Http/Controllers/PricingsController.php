<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CourierType;
use App\Models\DeliveryMode;
use Illuminate\Http\Request;
use App\Models\Pricing;
use Illuminate\Support\Facades\Auth; 

class PricingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Pricing::with('courier_type','sender_city','receiver_city','delivery_mode')->get();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "All pricings loaded";
        $res->data = $all;
        if ($request->is('dashboard/*')){
            return view('pricings.index')->with('pricings',$all);
        }
        return response()->json($res, 200);
    }

    public function  create(){
        $courier_types = CourierType::all();
        $cities = City::all();
        $delivery_modes = DeliveryMode::all();
        return view('pricings.create')
            ->with('courier_types',$courier_types)
            ->with('cities',$cities)
            ->with('delivery_modes',$delivery_modes);
    }

    public function edit($id){
        $pricing = Pricing::find($id);
        $courier_types = CourierType::all();
        $cities = City::all();
        $delivery_modes = DeliveryMode::all();

        return view('pricings.edit')
            ->with('pricing',$pricing)
            ->with('courier_types',$courier_types)
            ->with('cities',$cities)
            ->with('delivery_modes',$delivery_modes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'courier_type_id' => 'integer|required|exists:courier_types,id',
            /*'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',*/
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'min_weight' => 'numeric|required',
            'max_weight' => 'numeric|required',
            'price' => 'numeric|required'
        ]);
        $validated['user_id'] = $request->user_id;
        $validated['addedby'] = Auth::user()->id;
        $res = new \stdClass();
        try{
            $pricing = Pricing::create($validated);
            $res->error = false;
            $res->message = "Pricing created!";
            $res->data = [$pricing];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/pricing')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rec = Pricing::findOrFail($id);
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Pricing loaded";
        $res->data = [$rec];
        return response()->json($res, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'courier_type_id' => 'integer|exists:courier_types,id',
            /*'sender_city_id' => 'integer|exists:cities,id',
            'receiver_city_id' => 'integer|exists:cities,id',*/
            'delivery_mode_id' => 'integer|exists:delivery_modes,id',
            'min_weight' => 'numeric',
            'max_weight' => 'numeric',
            'price' => 'numeric'
        ]);
        $pricing = Pricing::findOrFail($id);
        $validated['user_id'] = $request->user_id;
        $validated['addedby'] = Auth::user()->id;
        $res = new \stdClass();
        try{
            $pricing->update($validated);
            $res->error = false;
            $res->message = "Pricing updated!";
            $res->data = [$pricing];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/pricing')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $rec = Pricing::findOrFail($id);
        $res = new \stdClass();
        try{
            $rec->delete();
            $res->error = false;
            $res->message = "Pricing deleted";
            $res->data = [$rec];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/pricing')->with('message',$res->message);
            }
            return response()->json($res, 204);
        }catch(\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = [$rec];
            return response()->json($res, 500);
        }
    }
}
