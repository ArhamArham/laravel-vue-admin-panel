<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;
use App\Profile;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\UserResources;
use App\Http\Resources\User as UsersCol;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $per_page=$request->per_page;
        $sortBy=$request->sort_by;
        $orderBy=$request->order_by;
        return response()->json([
            'users'=>new UserResources(User::orderBy($sortBy,$orderBy)->paginate($per_page)),
            'roles'=>Role::pluck('name')->all()
        ], 200);
    }
    public function admindetails()
    {
        return response()->json([
            'user'=>new UsersCol(User::where('id',Auth::user()->id)->first()),
        ], 200);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $role=Role::where('name',$request->role)->first();
        $user=new User([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password)
        ]);
        $user->role()->associate($role);
        $user->save();
        $user->profile()->save(new Profile());
        return response()->json(['user'=> new UsersCol($user)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $users=User::where('name','LIKE','%'.$id.'%')->paginate();
        return response()->json(['users'=>new UserResources($users)], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        $new_role=Role::where('name',$request->role)->first();
        $user=User::find($id);
        $user->name=$request->name;
        $user->email=$request->email;
        $user->role()->dissociate($user->role);
        $user->role()->associate($new_role);
        $user->save();
        return response()->json(['user'=>new UsersCol($user)], 200);
    }
    public function changeRole(Request $request)
    {   
        $new_role=Role::where('name',$request->role)->first();
        $user=User::find($request->user);
        if(Auth::user()->id == $user->id){
            return response()->json(['user'=>new UsersCol($user)], 422);    
        }else{
            $user->role()->associate($new_role);
            $user->save();
            return response()->json(['user'=>new UsersCol($user)], 200);
        }
    }
    public function changePhoto(Request $request)
    {   
            $user=User::find($request->user);
            $profile=Profile::where('user_id',$request->user)->first();
            $ext=$request->photo->extension();
            $image=Str::random(20).'.'.$ext;
            $photo=$request->photo->move(public_path('/images'),$image);
            $profile->photo='images/'.$image;
            $user->profile()->save($profile);
            return response()->json(['user'=>new UsersCol($user)], 200);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user=User::find($id)->delete();
        Profile::where('user_id',$user->id)->delete();
        return response()->json(['user'=>new UsersCol($user)], 200);
    }
    public function verifyEmail(Request $request)
    {
        if($request->id){
            $request->validate([
                'email' => 'required|unique:users,email,'.$request->id,
            ]);
        }else{
            $request->validate([
                'email' => 'required|unique:users'
            ]);
        }
        return response()->json(['message'=>'Email availabe'], 200);
    }
    public function login(Request $request)
    {
        $credentials=$request->only('email','password');

        if(Auth::attempt($credentials)){
            $token=Str::random(80);
            Auth::user()->api_token=$token;
            Auth::user()->save();
            $admin=Auth::user()->isAdmin();
            return response()->json(['token'=>$token,'isAdmin'=>$admin],200);
        }
        return response()->json(['status'=>'Email or password is wrong'],403);
    }
    public function verify(Request $request)
    {
        return $request->user()->only('name','email');
    }
    public function settings()
    {
        return response()->json([
            'user'=>new UsersCol(User::where('id',Auth::user()->id)->first()),
            'roles'=>Role::pluck('name')->all()
        ], 200);
    }
    public function updatesettings(Request $request, $id)
    {   
        $new_role=Role::where('name',$request->role)->first();
        $user=User::find($id);
        $user->name=$request->name;
        $user->email=$request->email;
        $user->role()->dissociate($user->role);
        $user->role()->associate($new_role);
        if ($request->password ==! Null) {
            if(Hash::check($request->password, $user->password)) {
                if ($request->new_password == !Null) {
                    # code...
                    $user->password=bcrypt($request->new_password);
                    
                }
            }
            else
            {
                return response()->json(['message','Password doesnot match!!!'], 403);
            }    
        }
        $user->save();
        return response()->json(['user'=>new UsersCol($user)], 200);
    }
}
