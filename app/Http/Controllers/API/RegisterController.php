<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return $this->sendResponse($success, 'User registered successfully');
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;

            return $this->sendResponse($success, 'User logged in successfully');
        } else {
            return $this->sendError('Unauthorized', ['error' => 'Unauthorised']);
        }
    }

    public function getPAT(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $tokenName = $input['name'];
        $email = $input['email'];

        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError('Invalid User');
        }

        $token = $user->createToken($tokenName);

        $success['personalAccessToken'] = $token->accessToken;
        $success['tokenName'] = $tokenName;
        $success['email'] = $email;

        return $this->sendResponse($token->toArray(), 'Personal access token generated successfully');
    }
}
