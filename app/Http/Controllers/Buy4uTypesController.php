<?php

namespace App\Http\Controllers;

use App\Models\Buy4uType;
use Illuminate\Http\Request;
use App\Models\CourierType;
use Illuminate\Support\Str;

class Buy4uTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Buy4uType::all();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('buy4uTypes.index')->with('buy4u_types',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        return view('buy4uTypes.create');
    }


    public function edit($id){
        $buy4u_type = Buy4uType::find($id);
        return view('buy4uTypes.edit')->with('buy4u_type',$buy4u_type);
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
        $entry = new Buy4uType();
        $entry->title = $validated['title'];
        $entry->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];
            if ($request->is('dashboard/*')){
                return redirect('dashboard/buy4u_type')->with('message',$response->message);
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
        $entry = Buy4uType::where('id', $id)->get();
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
        $buy4u_request = Buy4uType::find($id);
        $buy4u_request->title = $validated['title'];
        $buy4u_request->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $buy4u_request->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$buy4u_request];
            if ($request->is('dashboard/*')){
                return redirect('dashboard/buy4u_type')->with('message',$response->message);
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
    public function destroy(Request $request, $id)
    {
        $record = Buy4uType::findOrFail($id);
        $response = new \stdClass();
        try{
            $record->delete();
            $response->error = false;
            $response->message = "Record deleted!";
            $response->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('dashboard/buy4u_type')->with('message',$response->message);
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
        $courier_type = Buy4uType::where('slug', $slug)->first();
        $delivery_modes = DeliveryMode::where('buy4u_type_id', $courier_type->id)->get();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Records loaded!";
        $res->data = $delivery_modes;
        return response()->json($res, 200);
    }
}
