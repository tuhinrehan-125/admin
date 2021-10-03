<?php

namespace App\Http\Controllers;

use App\Models\Buy4uProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Buy4uProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->type == 'admin'){
            $buy4u_products = Buy4uProduct::all();
        }else if($user->type == 'rider'){
            $buy4u_products = Buy4uProduct::where('rider_id', Auth::id())->get();
        }else if($user->type == 'user'){
            $buy4u_products = Buy4uProduct::where('customer_id', Auth::id())->get();
        }
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Buy4u Products loaded!";
        $res->data = $buy4u_products;
        if ($request->is('dashboard/*')) {
            return view('buy4uProducts.index')->with('data',$res->data);
        }
        return response()->json($res, 200);
    }

    public function create(){
        return view('buy4uProducts.create');
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
            'buy4u_type_id' => 'integer|required|exists:buy4u_types,id',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'rider_id' => 'integer|required|exists:users,id',
            'address' => 'string|required',
            'preferred_shop_name' => 'string',
            'preferred_shop_address' => 'string',
            'status_id'=>'string|required|exists:statuses.id',
            'pricing_id' => 'integer|required|exists:pricings.id'
        ]);
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        $validated['branch_id'] = Branch::where('area_id', $validated['area_id'])->first()->id;
        if(!$validated['branch_id']){
            $validated['branch_id'] = Branch::where('city_id', $validated['city_id'])->first()->id;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }
        $res = new \stdClass();
        try{
            $buy4u_request = Buy4uRequest::create($validated);
            $res->error = false;
            $res->message = "Buy4u request sent!";
            $res->data = [$buy4u_request];

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard.buy4u_request')->with('message',$res->message);
            }

            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
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
        $rec = Buy4uRequest::findOrFail($id);
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier Request Loaded";
        $res->data = [$rec];
        return response()->json($res, 200);
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
            'buy4u_type_id' => 'integer|required|exists:buy4u_types,id',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'rider_id' => 'integer|required|exists:users,id',
            'address' => 'string|required',
            'preferred_shop_name' => 'string',
            'preferred_shop_address' => 'string',
            'status_id'=>'string|required|exists:statuses.id',
            'pricing_id' => 'integer|required|exists:pricings.id'
        ]);
        $res = new \stdClass();
        $buy4u_request = Buy4uRequest::findOrFail($id);
        try{
            $buy4u_request->update($validated);
            $res->error = false;
            $res->message = "Buy4u request updated!";
            $res->data = [$buy4u_request];
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $buy4u_request = Buy4uRequest::findOrFail($id);
        $res = new \stdClass();
        try{
            $buy4u_request->delete();
            $res->error = false;
            $res->message = "Buy4u Request Deleted!";
            $res->data = [$buy4u_request];
            return response()->json($res, 204);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    public function get_pricing(Request $request){
        $validated = $request->validate([
            'courier_type_id' => 'integer|required|exists:courier_types,id',
            'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'approximate_weight' => 'numeric|required|min:0.1',
        ]);
        $pricing = Pricing::where('min_weight', '<', $validated['approximate_weight'])
            ->where('max_weight', '>=', $validated['approximate_weight'])
            ->where('courier_type_id', $validated['courier_type_id'])
            ->where('sender_city_id', $validated['sender_city_id'])
            ->where('receiver_city_id', $validated['receiver_city_id'])
            ->where('delivery_mode_id', $validated['delivery_mode_id'])->first();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Pricing loaded";
        $res->data = $pricing;
        return response()->json($res, 200);
    }
}
