<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMode;
use Illuminate\Http\Request;
use App\Models\PackagingType;
use Illuminate\Support\Str;

class PackagingTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = PackagingType::all();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if($request->is('dashboard/*')){
            return  view('packageTypes.index')->with('package_types',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        return view('packageTypes.create');
    }

    public function edit($id){
        $package_type = PackagingType::find($id);
        return view('packageTypes.edit')->with('package_type',$package_type);
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
        $entry = new PackagingType();
        $entry->title = $validated['title'];
        $entry->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry created!";
            $response->data = [$entry];
            if($request->is('dashboard/*')){
                return  redirect('/dashboard/package_type')->with('message',$response->message);
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
        $entry = PackagingType::where('id', $id)->get();
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
        $entry =  PackagingType::find($id);
        $entry->title = $validated['title'];
        $entry->slug = Str::slug($validated['title']);
        $response = new \stdClass();
        try{
            $entry->save();
            $response->error = false;
            $response->message = "Entry updated!";
            $response->data = [$entry];
            if($request->is('dashboard/*')){
                return  redirect('/dashboard/package_type')->with('message',$response->message);
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
        $record = PackagingType::findOrFail($id);
        $response = new \stdClass();
        try{
            $record->delete();
            $response->error = false;
            $response->message = "Record deleted!";
            $response->data = [$record];
            if($request->is('dashboard/*')){
                return  redirect('/dashboard/package_type')->with('message',$response->message);
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
