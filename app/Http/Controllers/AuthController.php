<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\APIPasswordResetNotification;
use App\Models\APIPasswordResetToken;
use Carbon\Carbon;

class AuthController extends Controller
{
  //
  public function register(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
    ]);


    //return response()->json($validatedData, 200);


    $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => Hash::make($validatedData['password']),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'access_token' => $token,
      'token_type' => 'Bearer',
    ]);
  }

  public function login(Request $request)
  {
    if (!Auth::attempt($request->only('email', 'password'))) {
      return response()->json([
        'message' => 'Invalid login details'
      ], 401);
    }

    $user = User::where('email', $request['email'])->firstOrFail();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'access_token' => $token,
      'token_type' => 'Bearer',
    ]);
  }

  public function logout(Request $request)
  {
    /*
    // Revoke all tokens...
    $user->tokens()->delete();

    // Revoke the token that was used to authenticate the current request...
    $request->user()->currentAccessToken()->delete();

    // Revoke a specific token...
    $user->tokens()->where('id', $tokenId)->delete();
    */
    return $request->user()->currentAccessToken()->delete();
  }

  public function me(Request $request)
  {
    //return $request->user()->with('foods')->get();
    return $request->user();
  }

  public function updateUserDetails(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required | email | unique:users,email,' . $request->user()->id
    ]);

    $request->user()->name = $validatedData['name'];
    $request->user()->email = $validatedData['email'];
    $request->user()->update();
    return $request->user();
  }

  public function changePassword(Request $request)
  {

    $validatedData = $request->validate([
      'oldPassword' => 'required|string|min:8',
      'password' => 'required|string|min:8',
      'passwordConfirm' => 'required|string|min:8',
    ]);

    if (Hash::check($validatedData['oldPassword'], $request->user()->password)) { // if pass ok

      $request->user()->password = Hash::make($validatedData['password']);
      $request->user()->update();
      return response()->json([
        'message' => 'Password changed successfully'
      ], 200);
    } else {
      return response()->json([
        'message' => 'Invalid credential details'
      ], 401);
    }
  }

  //
  public function sendPasswordResetToken(Request $request)
  {
    $validatedData = $request->validate([
      'email' => 'required | email '
    ]);

    $user = User::where('email', $validatedData['email'])->firstOrFail();
    return $this->sendPassResetMail($user);
  }

  //mover a un trait o algo,,,,?
  public function sendPassResetMail(User $user)
  {   //generamos un codigo alphanumerico
    $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 6); //mejorar alphanumeric
    //creamos el token hasheando el alphanumerico y lo guardamos
    APIPasswordResetToken::create(
      [
        'user_id' => $user->id,
        'token_signature' => hash('md5', $code),
        'expires_at' => Carbon::now()->addMinutes(30),
      ]
    );
    //le enviamos el mail con el codigo sin hashear, con una validez de 30 min
    try {
      $user->notify(new APIPasswordResetNotification($code));
      return response()->json([
        'message' => 'Mail sent'
      ], 200);
    } catch (\Throwable $th) {
      return response()->json([
        'message' => $th
      ], 401);
    }
  }

  public function resetPassword(Request $request)
  {
    $validatedData = $request->validate([
      'email' => 'required|string|email|max:255|exists:users',
      'code' => 'required|string|max:255',
      'password' => 'required|string|max:255',
      'passwordConfirm' => 'required|string|max:255',
    ]);
    $user = User::where('email', $validatedData['email'])->first();
    if (!$user) { // si no hay mail....
      return response()->json([
        'message' => 'Invalid email'
      ], 401);
    }
    //
    $hashedCode = hash('md5', $validatedData['code']);
    //
    $token = $user->resetTokens->where('token_signature', $hashedCode)
      ->where('user_id', $user->id)->where('used_token', null)->first();
    if (!$token) {
      return response()->json([
        'message' => 'Invalid token'
      ], 401);
    }
    if (Carbon::now()->greaterThan($token->expires_at) || $token->used_token) {
      return response()->json([
        'message' => 'Token exipred'
      ], 401);
    }
    if ($token) { //&& !$token->used_token
      $token->used_token = true;
      $token->update();
      $user->password = Hash::make($validatedData['password']);
      $user->update();
      return response()->json([
        'message' => 'Password reset ok, Please login with new credentials'
      ], 200);
    }
  }
}
