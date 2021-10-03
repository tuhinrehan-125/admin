<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RiderArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
 
class AuthController extends Controller
{
    public function cusRegister(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'name' => ['string','required'],
            'email' => ['email','required','unique:users','email'],
            'phone' => ['string','required','unique:users'],
            'password' => ['string','required','min:6','confirmed'],
            'profile_picture' => ['file','mimes:jpg,png,svg,bmp,jfif'],
            'type' => ['string','required','in:user,delivery_rider,pickup_rider,manager,admin,individual,merchant'],
            'gender' => ['string','in:male,female,other'],
        ]);

        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()[0]]);
        }

        if($request->hasFile('profile_picture')){
            // handle file
            $file = $request->file('profile_picture');
            $file->move('images/profile_picture/', $file->getClientOriginalName());
            $profile_picture = 'images/profile_picture/'.$file->getClientOriginalName();
        }else{
            $profile_picture = 'images/profile_picture/no-image.jpg';
        }

        $user= User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'gender' => $request->gender,
            'preferred_method' => $request->preferred_method,
            'bkash_no' => $request->bkash_no,
            'nagad_no' => $request->nagad_no,
            'rocket_no' => $request->rocket_no,
            'bank_ac_no' => $request->bank_ac_no,
            'bank_name' => $request->bank_name,
            'bank_branch' => $request->bank_branch,
            'merchant_shop_area' => $request->merchant_shop_area,
            'merchant_shop_city' => $request->merchant_shop_city,
            'merchant_shop_address' => $request->merchant_shop_address,
            'profile_picture' => $profile_picture,
            'free_req' => '3',
        ]);

        $token= $user->createToken(config('app.key'))->plainTextToken;
        return response()->json(['token'=>$token,'user'=>$user]);
        
    }

    public function registration(Request $request)
    {

        $this->validate($request,[

            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|numeric|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'profile_picture' => 'file|mimes:jpg,png,svg,bmp,jfif',
            'type' => 'string|required',
            'gender' => 'string',
            
        ]);

        $users = $request->except('_token');
        if (isset($request->rider_area)) {
            $users['rider_area'] = implode(',', $request->rider_area);
        }
        
        if(!empty($request->password))
        {
            $users['password'] = bcrypt($request->password); 
        }

        if($request->hasFile('profile_picture')){
            $file = $request->file('profile_picture');
            $file->move('images/profile_picture/', $file->getClientOriginalName());
            $users['profile_picture'] = 'images/profile_picture/'.$file->getClientOriginalName();
        }else{
            $users['profile_picture'] = 'images/profile_picture/no-image.jpg';
        }
        
        if(!empty($request->pickup_rider_commission)){
            $users['pickup_rider_commission'] = $request->pickup_rider_commission; 
        }else{
            $users['pickup_rider_commission'] = '0';
        }

        if(!empty($request->delivery_rider_commission)){
            $users['delivery_rider_commission'] = $request->delivery_rider_commission; 
        }else{
            $users['delivery_rider_commission'] = '0';
        }

        if(!empty($request->pickup_agent_commission)){
            $users['pickup_agent_commission'] = $request->pickup_agent_commission; 
        }else{
            $users['pickup_agent_commission'] = '0';
        }

        if(!empty($request->delivery_agent_commission)){
            $users['delivery_agent_commission'] = $request->delivery_agent_commission; 
        }else{
            $users['delivery_agent_commission'] = '0';
        }
        
        
            $user= User::create($users);

            if (isset($request->rider_area)) {
               foreach ($request->rider_area as $area_id) {
                    $areas['area_id'] = $area_id;
                    $areas['user_id'] = $user->id;
                    RiderArea::create($areas);
               }
           }

        return redirect()->back()->with('message','User Added!');
        
        
    }

    public function register(Request $request){
        $validated = $request->validate([ 
            'name' => 'string|required',
            'email' => 'email|required|unique:users,email',
            'phone' => 'string|required',
            'password' => 'string|required|min:6|confirmed',
            'profile_picture' => 'file|mimes:jpg,png,svg,bmp,jfif',
            'type' => 'string|required',
            'gender' => 'string|in:male,female,other'
        ]);
        $validated['password'] = Hash::make($validated['password']);
        if($request->hasFile('profile_picture')){
            // handle file
            $file = $request->file('profile_picture');
            $file->move('images/profile_picture/', $file->getClientOriginalName());
            $validated['profile_picture'] = 'images/profile_picture/'.$file->getClientOriginalName();
        }else{
            $validated['profile_picture'] = 'images/profile_picture/no-image.jpg';
        }

        try{
            $user = User::create($validated);
            $token = $user->createToken(config('app.key'))->plainTextToken;
            if ($request->is('dashboard/*')) {
                return redirect()->back()->with('message','User created');
            }
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        }catch (\Exception $error){
            return response()->json(['message' => $error->getMessage(), 'error_stack' => $error], 500);
        }
    }
    public function login(Request $request){
        $validated = $request->validate([
            'phone' => 'required',
            'password' => 'required|string|min:6'
        ]);
        $user = User::where('phone', $validated['phone'])->first();
        if(!$user || !Hash::check($validated['password'], $user->password)){
            return response()->json([
                'error' => true,
                'message' => 'Could not authenticate!'
            ], 401);
        }
        if ($user->status == "0") {
            return response()->json([
                'error' => true,
                'message' => 'Could not authenticate!'
            ], 401);
        }
        $token = $user->createToken(config('app.key'))->plainTextToken;
        return response()->json([
            'error' => false,
            'message' => 'User logged in!',
            'user' => $user,
            'token' => $token
        ],200);
    }

    public function validate_token(Request $request){
        $user = Auth::user();
        if(!$user){
            return response()->json([
                'error' => true,
                'message' => "Invalid Token!",
            ], 401);
        }
        return response()->json([
            'error' => false,
            'message' => "The token is valid!",
            'user' => $user
        ], 200);
    }

    public function logout(Request $request){
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'User logged out!'
        ], 200);
    }

    public function profile(Request $request){
        $user = Auth::user();
        if($user){
            return response()->json([
                'data' => [$user]
            ], 200);
        }else{
            return response()->json(['error' => true, 'message' => 'Could not find user!'], 500);
        }

    }

    public function index(){
        $users = User::where('type','rider')->get();
        
        return view('users.index')->with('users',$users);
    }

    public function staff(){
        $users = User::orwhere('type','hr')->orwhere('type','account')->orwhere('type','care')->orwhere('type','marketing')->orwhere('type','bkash_agent')->get();
        return view('users.staff')->with('users',$users);
    }
    
    public function supervisor(){
        $users = User::where('type','manager')->get();
        return view('users.supervisor')->with('users',$users);
    }

    public function merchant(Request $request){
        $user = new User();
  
        if($request->has('name') && $request->name != null){
            $user = $user->where('name','like','%'.$request->name.'%');
        }
        if($request->has('email') && $request->email != null){
            $user = $user->where('email',$request->email); 
        }
        if($request->has('phone') && $request->phone != null){
            $user = $user->where('phone',$request->phone);
        }
        if($request->has('merchant_id') && $request->merchant_id != null){
            $user = $user->where('id',$request->merchant_id);
        }
        
        $user = $user->where('type','merchant')->orderBy('id','desc')->paginate(20);
        

        if (isset($request->name) || $request->email || $request->phone || $request->merchant_id) {
            $render['name'] = $request->name;
            $render['email'] = $request->email;
            $render['phone'] = $request->phone;
            $render['merchant_id'] = $request->merchant_id;
            $user = $user->appends($render);
        }

        $data['users'] = $user;


        /*$users = User::orwhere('type','merchant')->orwhere('type','individual')->get();*/
        return view('users.merchant',$data);
    }


    public function create(){
        return view('users.create');
    }

    public function edit($id){
        $user = User::find($id); 
        return view('users.edit')->with('user',$user);
    }

    public function update_user(Request $request,$id){
        $users = User::find($id);
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$users->id,
            'phone' => 'string|unique:users,phone,'.$users->id,
            'profile_picture' => 'file|mimes:jpg,png,svg,bmp',
            'type' => 'string',
            'gender' => 'string|in:male,female,other',
        ]);
        $users_info = $request->except('_token','password','password_confirmation');
        if(!empty($request->password))
        {
            $users_info['password'] = bcrypt($request->password); 
        }
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $file->move('images/profile_picture/', $file->getClientOriginalName());
            File::delete($users->profile_picture);
            $users_info['profile_picture'] = 'images/profile_picture/'.$file->getClientOriginalName();
        }
        if (isset($request->rider_area)) {
            $users_info['rider_area'] = implode(',', $request->rider_area);
        }
        User::where('id', $id)->update($users_info);
        if (isset($request->rider_area)) {
            $riderareas = RiderArea::where('user_id',$id)->get();
                foreach ($riderareas as $riderarea) {
                    $riderarea->delete();
                }
            foreach ($request->rider_area as $area_id) {
                $areas['area_id'] = $area_id;
                $areas['user_id'] = $id;
                RiderArea::create($areas);
            }
       }

        return redirect()->back()->with('message','User profile updated!');
    }
    public function update_profile2(Request $request){
        $validated = $request->validate([
            'name' => 'string', 
            'email' => 'email',
            'phone' => 'string',
            'password' => 'string|min:6|confirmed',
            'profile_picture' => 'file|mimes:jpg,png,svg,bmp',
            'type' => 'string|in:user,rider,manager,admin',
            'gender' => 'string|in:male,female,other'
        ]); 
        $user = Auth::user();
        foreach ($validated as $key => $value){
            if($key == 'password'){
                $validated['password'] = Hash::make($validated['password']);
            }
            if($key == 'profile_picture'){continue;}
            $user->$key = $value;
        }
        if($request->hasFile('profile_picture')){
            //remove previous file
            $file_name = explode('/storage/profile_picture/',$user->profile_picture);
            if(Storage::exists('/public/profile_picture/'.$file_name[1])){
                Storage::delete('/public/profile_picture/'.$file_name[1]);
            }
            // handle file
            $originalFileName = $request->file('profile_picture')->getClientOriginalName();
            $fileExt = $request->file('profile_picture')->getClientOriginalExtension();
            $originalFileNameWithoutExt = Str::of($originalFileName)->basename('.'.$fileExt);
            $fileNameToSave = $originalFileNameWithoutExt . '_' . time() . '.' . $fileExt;
            $validated['profile_picture'] = '/storage/profile_picture/'.$fileNameToSave;
            $request->file('profile_picture')->storeAs('public/profile_picture', $fileNameToSave);
        }else{
            $validated['profile_picture'] = '/storage/profile_picture/no-image.jpg';
        }
        try{
            $user->update($validated);
            if ($request->is('dashboard/*')){
                return  redirect('/dashboard/users')->with('message','User profile updated!');
            }
            return response()->json([
                'error' => false,
                'message' => 'User profile updated!',
                'data' => [$user]
            ], 201);
        }catch (\Exception $error){
            return response()->json(['message' => $error->getMessage(), 'error_stack' => $error], 500);
        }
    }

    public function delete($id){
        $user = User::find($id);
        File::delete($user->profile_picture);
        $user->delete();
        return redirect(route('dashboard.users'))->with('message','user deleted');
    }

    public function update_profile(Request $request)
    {
        $users = User::find(Auth::user()->id);
        $validator = \Validator::make($request->all(),[
            'name' => ['string','required'],
            'email' => ['email','required','unique:users,email,'.$users->id],
            'phone' => ['string','required','unique:users,phone,'.$users->id],
            'password' => ['string','min:6','confirmed'],
            'profile_picture' => ['file','mimes:jpg,png,svg,bmp,jfif'],
            'type' => ['string','required','in:user,rider,manager,admin,individual,merchant'],
            'gender' => ['string','in:male,female,other'],
        ]);

        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()[0]]);
        }

        if($request->hasFile('profile_picture')){
            // handle file
            $file = $request->file('profile_picture');
            $file->move('images/profile_picture/', $file->getClientOriginalName());
            File::delete($users->profile_picture);
            $profile_picture = 'images/profile_picture/'.$file->getClientOriginalName();
            $user= User::where('id', Auth::user()->id)->update(['profile_picture' => $profile_picture]);
        }

        $users_info['name'] = $request->name;
        $users_info['email'] = $request->email;
        $users_info['phone'] = $request->phone;
        $users_info['type'] = $request->type;
        $users_info['gender'] = $request->gender;
        $users_info['bkash_no'] = $request->bkash_no;
        $users_info['nagad_no'] = $request->nagad_no;
        $users_info['rocket_no'] = $request->rocket_no;
        $users_info['bank_ac_no'] = $request->bank_ac_no;
        $users_info['bankAC_name'] = $request->bankAC_name;
        $users_info['bank_name'] = $request->bank_name;
        $users_info['bank_branch'] = $request->bank_branch;
        $users_info['merchant_shop_area'] = $request->merchant_shop_area;
        $users_info['merchant_shop_city'] = $request->merchant_shop_city;
        $users_info['merchant_shop_address'] = $request->merchant_shop_address;
        $users_info['preferred_method'] = $request->preferred_method;
        if(!empty($request->password))
        {
            $users_info['password'] = bcrypt($request->password); 
        }
        User::where('id', Auth::user()->id)->update($users_info);
        


        /*$user= User::where('id', Auth::user()->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'gender' => $request->gender,
            'bkash_no' => $request->bkash_no,
            'bank_ac_no' => $request->bank_ac_no,
            'bank_name' => $request->bank_name,
            'bank_branch' => $request->bank_branch,
            'merchant_shop_area' => $request->merchant_shop_area,
            'merchant_shop_city' => $request->merchant_shop_city,
            'merchant_shop_address' => $request->merchant_shop_address,
            'preferred_method' => $request->preferred_method,
        ]);*/

        $response = 
        ['msg' => 'Update Successfully'];
        return response()->json($response, 200);   
    }
    
    public function device(Request $request,$id){
        $users_info['device_id'] = $request->device_id;
        User::where('id',$id)->update($users_info);
        $response = 
        ['msg' => 'Update Successfully'];
        return response()->json($response, 200);  
    }
    
    public function merchant_special_request(Request $request){
        $user = User::findOrFail($request->user_id);
        $data['speical'] = $request->status;
        $data['speical_addedby'] = Auth::user()->id;
        User::where('id',$request->user_id)->update($data);
        return response()->json(['success'=>'Status change successfully.']);
    }
    
    public function merchant_yes_special_request($id,$sid){
        $data['speical'] = $sid;
        $data['speical_addedby'] = Auth::user()->id;
        User::where('id',$id)->update($data);
        
        return redirect()->back()->with('message','Successfully Updated');
    }
    
    public function change_password(Request $request){
        $validatedData = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $users_info = $request->except('password_confirmation');
        $users_info['password'] = bcrypt($request->password); 
        User::where('id', Auth::user()->id)->update($users_info);
        $response = 
        ['msg' => 'Update Successfully'];
        return response()->json($response, 200); 
    }
    

}
