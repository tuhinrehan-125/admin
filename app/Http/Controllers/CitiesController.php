<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Str;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = City::orderBy('name','ASC')->get();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('cities.index')->with('cities',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        return view('cities.create');
    }

    public function edit($id){
        $city  = City::find($id);
        return view('cities.edit')->with('city',$city);
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
            'name' => 'required|string'
        ]);
        $entry = new City();
        $entry->name = $validated['name'];
        $entry->slug = Str::slug($validated['name']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];

            if ($request->is('dashboard/*')){
                return redirect('dashboard/city')->with('message',$response->message);
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
        $entry = City::where('id', $id)->get();
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
            'name' => 'required|string'
        ]);
        $entry = City::find($id);
        $entry->name = $validated['name'];
        $entry->slug = Str::slug($validated['name']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry Updated!";
            $response->data = [$entry];

            if ($request->is('dashboard/*')){
                return redirect('dashboard/city')->with('message',$response->message);
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
    public function destroy(Request  $request,$id)
    {
        $record = City::findOrFail($id);
        $response = new \stdClass();
        try{
            $record->delete();
            $response->error = false;
            $response->message = "Record deleted!";
            $response->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('dashboard/city')->with('message',$response->message);
            }
            return response()->json($response, 204);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = "Could not delete record! | ". $error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
        }
    }

    public function areas(Request $request, $slug){
        $city = City::where('slug', $slug)->first();
        $areas = Area::where('city_id', $city->id)->orderBy('slug','ASC')->get();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Records loaded!";
        $res->data = $areas;
        return response()->json($res, 200);
    }
}
