<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;

class StatusController extends Controller
{
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'string|required',
            'sequence' => 'integer|required'
        ]);
        $res = new \stdClass();
        try{
            $status = Status::create($validated);
            $res->error = false;
            $res->message = "Status created!";
            $res->data = [$status];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/status')->with('message', $res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    public function index(Request $request){
        $statuses = Status::all();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "All statuses loaded!";
        $res->data = $statuses;
        if ($request->is('dashboard/*')){
            return view('statuses.index')->with('statuses',$statuses);
        }
        return response()->json($res, 200);
    }

    public function create(){
        return view('statuses.create');
    }

    public function edit($id){
        $status = Status::find($id);
        return view('statuses.edit')->with('status',$status);
    }

    public function show(Request $request, $id){
        $status = Status::findOrFail($id);
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Status with id (".$id.") loaded!";
        $res->data = [$status];
        return response()->json($res, 200);
    }

    public function update(Request $request, $id){
        $status = Status::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string',
            'sequence' => 'integer'
        ]);
        $res = new \stdClass();
        try {
            $status->update($validated);
            $res->error = false;
            $res->message = "Status with id (".$id.") updated!";
            $res->data = [$status];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/status')->with('message', $res->message);
            }
            return response()->json($res, 201 );
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    public function destroy(Request $request, $id){
        $status = Status::findOrFail($id);
        $res = new \stdClass();
        try {
            $status->delete();
            $res->error = false;
            $res->message = "Status with id (".$id.") deleted!";
            $res->data = [$status];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/status')->with('message', $res->message);
            }
            return response()->json($res, 204);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }
}
