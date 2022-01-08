<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //validate user credentials
        $fields = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        // check if user exists
        if (!$user) {
            return response()->json(
                ['status' => 'false', 'error' => 'user does not exist'],
                404
            );
        }

        // check if email has been verified
        if (!empty($user->otp)) {
            return response()->json([
                'status' => 'false',
                'error' => 'mail not verified',
                'user' => $user->id
            ], 301);
        }

        $credentials = [
            'email' => $fields['email'],
            'password' => $fields['password']
        ];

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('access_token')->accessToken;

            return response()->json(
                ['status' => 'true', 'token' => $token],
                200
            );
        } else {
            return response()->json(
                ['status' => 'false', 'error' => 'wrong credentials'],
                401
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(UserRequest $request)
    {
        $password = bcrypt($request['password']);
        $otp = random_int(100000, 999999);

        Mail::to($request['email'])->send(new VerifyEmail($otp));

        // ensure that OTP is sent before creating the user account.
        if (count(Mail::failures()) > 0) {
            return Response::json(
                ['status' => 'false', 'message' => 'unable to send mail'],
                200
            );
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone_number' => $request['phone_number'],
            'otp' => $otp,
            'password' => $password
        ]);

        return new UserResource($user);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resendOTP($id)
    {

        // get orders from orders table where search parameter exists in the product column

        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['status' => 'false', 'error' => 'user does not exist'], 404);
        }

        if (empty($otp)) {
            return response()->json(
                ['status' => 'false', 'message' => 'email already verified'],
                400
            );
        }

        $otp = $user->otp;
        Mail::to($user->email)->send(new VerifyEmail($otp));

        if (count(Mail::failures()) > 0) {
            return response()->json(
                ['status' => 'false', 'message' => 'unable to send mail'],
                200
            );
        } else {
            return response()->json([
                'status' => 'true',
                'message' => "otp resent to $user->email ",
                'user' => $user
            ], 200);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function VerifyEmail(Request $request)
    {
        $fields = $request->validate([
            'otp' => 'required|integer|numeric|digits:6'
        ]);

        $user = User::where('otp', $fields['otp'])->first();

        if (!$user) {
            return response()->json(['status' => 'false', 'error' => 'invalid otp'], 422);
        }

        // $otp = $user->otp;

        $query = $user->update([
            'otp' => ''
        ]);

        if (!$query) {
            return response()->json(
                ['status' => 'false', 'message' => 'unable to verify email'],
                500
            );
        } else {
            return response()->json([
                'status' => 'true',
                'message' => "email verified successfully"
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user = auth()->user();

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function UpdateProfile(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:100|min:3',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11|max:14'
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(
                ['status' => 'false', 'message' => 'user not found'],
                404
            );
        }

        $query = $user->update([
            'name' => $fields['name'],
            'phone_number' => $fields['phone_number']
        ]);


        if ($query) {
            return new UserResource($user);
        } else {
            return response()->json(
                ['status' => 'false', 'message' => 'profile update failed'],
                500
            );
        }
    }
}
