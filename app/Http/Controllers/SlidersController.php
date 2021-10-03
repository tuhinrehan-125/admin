<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderCollectionSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SlidersController extends Controller
{  
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Slider::all();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All sliders loaded";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('sliders.index')->with('sliders',$all);
        }
        return response()->json($all, 200);
    }
    public function create(){
        return view('sliders.create');
    }

    public function edit($id){
        $slider = Slider::find($id);
        return view('sliders.edit')->with('slider',$slider);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,bmp,svg'
        ]);
        $originalFileName = $request->file('image')->getClientOriginalName();
        $fileExt = $request->file('image')->getClientOriginalExtension();
        $originalFileNameWithoutExt = Str::of($originalFileName)->basename('.'.$fileExt);
        $fileNameToSave = $originalFileNameWithoutExt . '_' . time() . '.' . $fileExt;
        $response = new \stdClass();
        $slider = new Slider();
        $slider->image = 'public/images/slider/'.$fileNameToSave;
        try{
            $request->file('image')->move('public/images/slider', $fileNameToSave);
            $slider->save();
            $response->error = false;
            $response->message = "Slider added";
            $response->data = [$slider];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/slider')->with($response->message);
            }
            return response()->json($response, 201);
        }catch (\Exception $error){
            $response->error = $error;
            $response->message = "Could not add slider image | ".$error->getMessage();
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
        //
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
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,bmp,svg'
        ]);
        $originalFileName = $request->file('image')->getClientOriginalName();
        $fileExt = $request->file('image')->getClientOriginalExtension();
        $originalFileNameWithoutExt = Str::of($originalFileName)->basename('.'.$fileExt);
        $fileNameToSave = $originalFileNameWithoutExt . '_' . time() . '.' . $fileExt;
        $response = new \stdClass();
        $slider =  Slider::find($id);
        $slider->image = 'public/images/slider/'.$fileNameToSave;
        try{
            //remove previous file
            $file_name = explode('public/images/slider/',$slider->image);
            if(Storage::exists('public/images/slider/'.$file_name[1])){
                Storage::delete('public/images/slider/'.$file_name[1]);
            }
            $request->file('image')->move('public/images/slider', $fileNameToSave);
            $slider->save();
            $response->error = false;
            $response->message = "Slider added";
            $response->data = [$slider];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/slider')->with($response->message);
            }
            return response()->json($response, 201);
        }catch (\Exception $error){
            $response->error = $error;
            $response->message = "Could not add slider image | ".$error->getMessage();
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
        $slider = Slider::find($id);
        $slider_collection_sliders = SliderCollectionSlider::where('slider_id', $id)->get();
        if (count($slider_collection_sliders)!=0){
            foreach ($slider_collection_sliders as $slider_collection_slider){
                $slider_collection_slider->delete();
            }
        }

        $response = new \stdClass();
        try{
            $file_name = explode('/storage/slider/',$slider->image);
            if(Storage::exists('/public/slider/'.$file_name[1])){
                Storage::delete('/public/slider/'.$file_name[1]);
            }
            $slider->delete();
            $response->error = false;
            $response->message = "Slider deleted";
            $response->data = [$slider];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/slider')->with($response->message);
            }
            return response()->json($response, 204);
        }catch (\Exception $error){
            $response->error = true;
            $response->message = "Could not delete slider | ".$error->getMessage();
            $response->data = $error;
            return response()->json($response, 500);
        }
    }
}
