<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderCollection;
use App\Models\SliderCollectionSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SliderCollectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Slider::with('slider_collections')->get();
        $slider_collection = SliderCollection::with('sliders')->get();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All sliders loaded";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('sliders_collection.index')
                ->with('slider_collection',$slider_collection)
                ->with('sliders',$all);
        }
        return response()->json($all, 200);
    }

    public function create(){
        return view('sliders_collection.create');
    }

    public function edit($id){
        $slider_collection = SliderCollection::where('id',$id)->first();
        $slider_collection_slider = SliderCollectionSlider::where('slider_collection_id',$id)->get();
        $slider_id = [];
        foreach ($slider_collection_slider as $pivot){

            array_push($slider_id,$pivot->slider_id);
        }

        $sliders = \DB::table('sliders')->join('slider_collection_sliders','slider_collection_sliders.slider_id','sliders.id')->where('slider_collection_sliders.slider_collection_id',$id)->get();
        return view('sliders_collection.edit')
            ->with('sliders',$sliders)
            ->with('slider_id',$slider_id)
            ->with('slider_collection',$slider_collection);
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
            'images' => 'required|array',
            'title' => 'required|string'
        ]);

        $slider_ids = [];
        foreach ($validated['images'] as $slider_image){
            $originalFileName = $slider_image->getClientOriginalName();
            $fileExt = $slider_image->getClientOriginalExtension();
            $originalFileNameWithoutExt = Str::of($originalFileName)->basename('.'.$fileExt);
            $fileNameToSave = $originalFileNameWithoutExt . '_' . time() . '.' . $fileExt;
            $slider = new Slider();
            $slider->image = 'public/images/slider/'.$fileNameToSave;
            $slider->save();
            $slider_image->move('public/images/slider/', $fileNameToSave);
            array_push($slider_ids, $slider->id);
        }
        $slider_collection = new SliderCollection();
        $slider_collection->title = $validated['title'];
        $slider_collection->slug = Str::slug($validated['title']);
        $slider_collection->save();
        foreach ($slider_ids as $slider_id){
            $slider_collection_slider = new SliderCollectionSlider();
            $slider_collection_slider->slider_collection_id = $slider_collection->id;
            $slider_collection_slider->slider_id = $slider_id;
            $slider_collection_slider->save();
        }
        $response = new \stdClass();
        $response->error = false;
        $response->message = "Slider added";
        $collection = new \stdClass();
        $collection->title = $slider_collection->title;
        $collection->slug = $slider_collection->slug;
        $collection->sliders = $slider_collection->sliders;
        $response->data = $collection;
        if ($request->is('dashboard/*')){
            return redirect('/dashboard/sliders_collection')->with($response->message);
        }
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $slider_collection = SliderCollection::where('slug', $slug)->first();
        $collection = new \stdClass();
        $collection->title = $slider_collection->title;
        $collection->slug = $slider_collection->slug;
        $collection->sliders = $slider_collection->sliders;
        $response = new \stdClass();
        $response->error = false;
        $response->message = "Sliders loaded";
        $response->data = $collection;
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
            'images' => 'required|array',
            'title' => 'required|string'
        ]);
        $slider_collection =  SliderCollection::find($id);
        $slider_collection->title = $validated['title'];
        $slider_collection->slug = Str::slug($validated['title']);
        $slider_collection->save();


        if (count($validated['images'])!=0){
            $slider_collection_sliders =  SliderCollectionSlider::where('slider_collection_id',$id)->get();
            foreach ($slider_collection_sliders as $slider_collection_slider){
                $slider_collection_slider->delete();
            }
            foreach ($validated['images'] as $slider_id){
                $slider_collection_slider = new SliderCollectionSlider();
                $slider_collection_slider->slider_collection_id = $id;
                $slider_collection_slider->slider_id = $slider_id;
                $slider_collection_slider->save();
            }
        }

        return redirect(route('dashboard.sliders.collection'))->with('message','slider collection updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $slider_collection = SliderCollection::find($id);
        $slider_collection_sliders = SliderCollectionSlider::where('slider_collection_id',$id)->get();
        foreach ($slider_collection_sliders as $pivot_table){
            try {
                $pivot_table->delete();
            }catch (\Exception $error){
                return redirect('/dashboard/sliders_collection')->with('message','error in operation');
            }

        }
        $response = new \stdClass();
        try{
            $slider_collection->delete();
            $response->error = false;
            $response->message = "Slider Collection deleted";
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/sliders_collection')->with($response->message);
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
