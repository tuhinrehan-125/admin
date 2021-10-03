<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlendxRouter extends Controller
{
    public function blendme(Request $request, $route, $action = 'index', $id = null){
        $model = BlendxHelpers::route_to_model($route);
        $singular_controller_name = $model->name."Controller";
        $plural_controller_name = Str::plural($model->name)."Controller";
        $plural_controller_path = "App\\Http\\Controllers\\".$plural_controller_name;
        $singular_controller_path = "App\\Http\\Controllers\\".$singular_controller_name;
        $controller_name = $plural_controller_name;
        $controller_path = $plural_controller_path;
        if(!class_exists($controller_path)){
            $controller_name = $singular_controller_name;
            $controller_path = $singular_controller_path;
        }
        if(!class_exists($controller_path)){
            $controller_name = "BlendxController";
            $controller_path = "App\\Http\\Controllers\\BlendxController";
        }

        if(method_exists($controller_path, $action)){
            return $controller_path::$action($request, $route, $id);
        }else{
            return response()->json(BlendxHelpers::generate_response(true, 'Method '.$action.' not found!', []), 500);
        }

    }
}
