<?php

namespace App\Http\Controllers;

use App\Models\Buy4uType;
use App\Models\CourierType;
use Illuminate\Http\Request;
use App\Models\DeliveryMode;
use PHPUnit\Exception;
use Illuminate\Support\Facades\Validator;

class DeliveryModesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = DeliveryMode::all();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "All records loaded!";
        $res->data = $records;
        if($request->is('dashboard/*')){
            return view('deliveryMode.index')->with('records',$records);
        }
        return response()->json($res, 200);
    }

    public function create(){
        $courier_types = CourierType::all();
        $buy4u_types = Buy4uType::all();
        return view('deliveryMode.create')
            ->with('courier_types',$courier_types)
            ->with('buy4u_types',$buy4u_types);
    }

    public function edit($id){
        $delivery_mode = DeliveryMode::find($id);
        $courier_types = CourierType::all();
        $buy4u_types = Buy4uType::all();
        return view('deliveryMode.edit')
            ->with('courier_types',$courier_types)
            ->with('delivery_mode',$delivery_mode)
            ->with('buy4u_types',$buy4u_types);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'courier_type_id' => ['required_unless:buy4u_type_id', 'exists:courier_types,id'],
            'buy4u_type_id' => ['required_unless:courier_type_id', 'exists:buy4u_types,id'],
            'title' => ['required', 'string'],
            'time_in_hours' => ['required', 'integer']
        ]);
        $res = new \stdClass();
        try{
            $record = DeliveryMode::create($request->all());
            $res->error = false;
            $res->message = "Record created.";
            $res->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/delivery_mode')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
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
        $record = DeliveryMode::findOrFail($id);
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Record loaded!";
        $res->data = [$record];
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
        $validated = Validator::make($request->all(), [
            'courier_type_id' => ['exists:courier_types,id'],
            'buy4u_type_id' => ['exists:buy4u_types,id'],
            'title' => ['string'],
            'time_in_hours' => ['integer']
        ]);
        $res = new \stdClass();
        try{
            $record = DeliveryMode::findOrFail($id);
            $record->update($request->all());
            $res->error = false;
            $res->message = "Record updated.";
            $res->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/delivery_mode')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $record = DeliveryMode::findOrFail($id);
        $res = new \stdClass();
        try{
            $record->delete();
            $res->error = false;
            $res->message = "Record deleted!";
            $res->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/delivery_mode')->with('message',$res->message);
            }
            return response()->json($res, 204);
        }catch(\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }
}
