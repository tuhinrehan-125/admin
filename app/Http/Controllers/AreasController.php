<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Str;

class AreasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Area::with('city')->orderBy('name','ASC')->get();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('areas.index')->with('areas',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        $cities = City::all();
        return view('areas.create')->with('cities',$cities);
    }

    public function edit($id){
        $area = Area::find($id);
        $cities = City::all();
        return view('areas.edit')
            ->with('area',$area)
            ->with('cities',$cities);
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
            'name' => 'required|string',
            'city_id' => 'required|integer|exists:cities,id'
        ]);
        $entry = new Area();
        $entry->name = $validated['name'];
        $entry->slug = Str::slug($validated['name']);
        $entry->city_id = Str::slug($validated['city_id']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/area')->with('message',$response->message);
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
        $entry = Area::where('id', $id)->get();
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
            'name' => 'required|string',
            'city_id' => 'required|integer|exists:cities,id'
        ]);
        $entry = Area::find($id);
        $entry->name = $validated['name'];
        $entry->slug = Str::slug($validated['name']);
        $entry->city_id = Str::slug($validated['city_id']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/area')->with('message',$response->message);
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
        $record = Area::findOrFail($id);
        $response = new \stdClass();
        try{
            $record->delete();
            $response->error = false;
            $response->message = "Record deleted!";
            $response->data = [$record];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/area')->with('message',$response->message);
            }
            return response()->json($response, 204);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = "Could not delete record! | ". $error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
        }
    }
}
