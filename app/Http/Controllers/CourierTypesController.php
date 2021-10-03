<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMode;
use Illuminate\Http\Request;
use App\Models\CourierType;
use Illuminate\Support\Str;

class CourierTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = CourierType::all();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if($request->is('dashboard/*')){
            return view('courierTypes.index')->with('courier_types',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        return view('courierTypes.create');
    }

    public function edit($id){
        $courier_type = CourierType::find($id);
        return view('courierTypes.edit')->with('courier_type',$courier_type);
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
            'title' => 'required|string'
        ]);
        $entry = new CourierType();
        $entry->title = $validated['title'];
        $entry->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];
            if($request->is('dashboard/*')){
                return redirect('/dashboard/courier_type')->with('message','Courier type created');
            }
            return response()->json($response, 201);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = $error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
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
        $entry = CourierType::where('id', $id)->get();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "Record loaded!";
        $response->data = $entry;
        return response()->json($response, 200);
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
            'title' => 'required|string'
        ]);
        $courier_type = CourierType::find($id);
        $courier_type->title = $validated['title'];
        $courier_type->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $courier_type->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$courier_type];
            if($request->is('dashboard/*')){
                return redirect('/dashboard/courier_type')->with('message','Courier type created');
            }
            return response()->json($response, 201);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = $error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
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
        $record = CourierType::findOrFail($id);
        $response = new \stdClass();
        try{
            $record->delete();
            $response->error = false;
            $response->message = "Record deleted!";
            $response->data = [$record];
            if($request->is('dashboard/*')){
                return redirect('/dashboard/courier_type')->with('message','Courier type created');
            }
            return response()->json($response, 204);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = "Could not delete record! | ". $error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
        }
    }

    public function delivery_modes(Request $request, $slug){
        $courier_type = CourierType::where('slug', $slug)->first();
        $delivery_modes = DeliveryMode::where('courier_type_id', $courier_type->id)->get();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Records loaded!";
        $res->data = $delivery_modes;
        return response()->json($res, 200);
    }
}
