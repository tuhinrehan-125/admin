<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlendxController extends Controller
{
    public static function index(Request $request, $route, $id = null){
        if(!$request->isMethod('GET')){
            return response("Method not allowed! Please make a GET request!", 405, ['Access-Control-Allow-Methods' => 'GET']);
        }
        $api = BlendxHelpers::is_api($request);
        $model = BlendxHelpers::route_to_model($route);
        $all = $model->path::all();
        $res = BlendxHelpers::generate_response(false, 'All '.$model->name.' loaded!', $all);
        return response()->json($res, 200);
    }

    public static function show(Request $request, $route, $id){
        if(!$request->isMethod('GET')){
            return response("Method not allowed! Please make a GET request!", 405, ['Access-Control-Allow-Methods' => 'GET']);
        }
        $model = BlendxHelpers::route_to_model($route);
        $entry = $model->path::findOrFail($id);
        $res = BlendxHelpers::generate_response(false, $model->name.' with id ('.$id.')loaded!', [$entry]);
        return response()->json($res, 200);
    }

    public static function delete(Request $request, $route, $id){
        if(!$request->isMethod('DELETE')){
            return response("Method not allowed! Please make a DELETE request!", 405, ['Access-Control-Allow-Methods' => 'DELETE']);
        }
        $model = BlendxHelpers::route_to_model($route);
        $entry = $model->path::findOrFail($id);
        try{
            $entry->delete();
            $res = BlendxHelpers::generate_response(false, 'Successfully deleted!', [$entry]);
            return response()->json($res, 204);
        }catch (\Exception $error){
            $res = BlendxHelpers::generate_response(true, 'Could not delete!', [$error->getMessage()]);
            return response()->json($res, 500);
        }
    }

    public static function store(Request $request, $route, $id){
        if(!$request->isMethod('POST')){
            return response("Method not allowed! Please make a POST request!", 405, ['Access-Control-Allow-Methods' => 'POST']);
        }
        $model = BlendxHelpers::route_to_model($route);
        if($model->blender){
            $x = $model->blender::store_validator();
        }
        $validated = $request->validate($x);
        $toCreate = $model->blender::after_validator($validated);
        try{
            $entry = $model->path::create($toCreate);
            $model->blender::after_created($entry);
            $res = BlendxHelpers::generate_response(false, 'Successfully created!', [$entry]);
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res = BlendxHelpers::generate_response(true, 'Could not create!', [$error->getMessage()]);
            return response()->json($res, 500);
        }
    }

    public static function update(Request $request, $route, $id){
        if(!$request->isMethod('PUT')){
            return response("Method not allowed! Please make a PUT request!", 405, ['Access-Control-Allow-Methods' => 'PUT']);
        }
        $model = BlendxHelpers::route_to_model($route);
        if($model->blender){
            $x = $model->blender::update_validator();
        }
        $validated = $request->validate($x);
        $toCreate = $model->blender::after_validator($validated);
        try{
            $entry = $model->path::findOrFail($id);
            $entry->update($toCreate);
            $model->blender::after_updated($entry);
            $res = BlendxHelpers::generate_response(false, 'Successfully updated!', [$entry]);
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res = BlendxHelpers::generate_response(true, 'Could not update!', [$error->getMessage()]);
            return response()->json($res, 500);
        }
    }
}
