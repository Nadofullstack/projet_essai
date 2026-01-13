<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    //
    public function redirect(){
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(){
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email'=>$googleUser->email()],
            [
                'name'=>$googleUser->name(),
                'password'=>bcrypt(Str::random(24))
            ]
            );

            //sanctum token
            $token = $user->createToken('google-auth')->plainTextToken;

            //redirection vers vue

         return redirect('http://localhost:5173/google-callback?token='.$token);
    }
}
