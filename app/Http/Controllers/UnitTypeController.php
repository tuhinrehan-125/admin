<?php

namespace App\Http\Controllers;

use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitTypeController extends Controller
{
    public function index(Request $request){
        $all = UnitType::all();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Unit types loaded";
        $res->data = $all;

        if ($request->is('dashboard/*')){
            return view('unitTypes.index')->with('unit_types',$all);
        }
        return response()->json($res, 200);
    }

    public function create(){
        return view('unitTypes.create');
    }
    public function edit($id){
        $unit_type = UnitType::find($id);
        return view('unitTypes.edit')->with('unit_type',$unit_type);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'string|required'
        ]);
        $res = new \stdClass();
        try{
            $entry = UnitType::create($validated);
            $res->error = false;
            $res->message = "Unit Type Created";
            $res->data = [$entry];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/unit_type')->with($res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    public function update(Request $request, $id){
        $validated = $request->validate([
            'title' => 'string|required'
        ]);
        $unit_type = UnitType::find($id);
        $res = new \stdClass();
        $unit_type->title = $validated['title'];
        try{
            $unit_type->save();
            $res->error = false;
            $res->message = "Unit Type Updated";
            $res->data = [$unit_type];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/unit_type')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    public function destroy($id){
        $unit_type = UnitType::find($id);
        $res = new \stdClass();
        try {
            $res->error = false;
            $res->message = "Unit Type Deleted";
            $unit_type->delete();
            return redirect('/dashboard/unit_type')->with('message',$res->message);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }


    }
}
