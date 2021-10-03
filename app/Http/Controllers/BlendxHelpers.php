<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlendxHelpers extends Controller
{
    public static function generate_response($error = false, $message, $data = []){
        $response = new \stdClass();
        $response->error = $error;
        $response->message = $message;
        $response->data = $data;
        return $response;
    }

    public static function is_api(Request $request){
        if($request->is("api/*")){
            return true;
        }else{
            return false;
        }
    }

    public static function route_to_model($route){
        $model_name = Str::ucfirst(Str::camel($route));
        $model_path = "App\\Models\\".$model_name;
        $blender_path = "App\\Blendx\\".$model_name;
        if(!class_exists($model_path)){
            return response()->json(BlendxHelpers::generate_response(true, 'Model not found!', []), 404);
        }
        if(!class_exists($blender_path)){
            $blender_path = null;
        }
        $model = new \stdClass();
        $model->name = $model_name;
        $model->path = $model_path;
        $model->blender = $blender_path;
        return $model;
    }
}
