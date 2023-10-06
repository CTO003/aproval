<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function index()
    {
        return view('auth.login',[
            'title' => 'Login'
        ]);
    }


    public function login(Request $request)
    {
        $this->validate($request, [
            'email'=>'required|email',
            'password'=>'required',
        ]);

        $credentials = request(['email', 'password']);

        if (auth()->attempt($credentials)) {
            $token = Auth::guard('api')->attempt($credentials);
            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau Password Salah'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'password' => 'required',
            'konfirmasi_password' => 'required_with:password|same:password',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        $member = Member::create($input);

        return response()->json([
            'data' => $member
        ]);
    }

    public function login_member(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        
        
        $member = Member::where('email', $request->email)->first();
        if($member){
            if(Hash::check($request->password, $member->password)){
                $request->session()->regenerate();
                return response()->json([
                    'message' => 'berhasil',
                    'data' => $member
            ]);
        }else{
            return response()->json([
                'message' => 'gagal',
                'data' => 'Password Salah'
            ]);
        }
        }else{
            return response()->json([
                'message' => 'gagal',
                'data' => 'Email Salah'
            ]);
        }
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }

    public function logout_member()
    {
        Session::flush();
        return redirect('/login');
    }
}
