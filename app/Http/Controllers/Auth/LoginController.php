<?php

namespace App\Http\Controllers\Auth;

use App\Actions\UserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repository\Eloquent\UserRepository;
use Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( private UserAction $userAction)
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'email' => ['string', 'email', 'max:255'],
            'phone' => ['string', 'min:8', 'max:15'],
            'phone_required' => Rule::requiredIf(fn() => !isset($data['email']) && !isset($data['phone'])),
        ], [
            'phone_required' => "phone or email required"
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $validator = $this->validator($data);
        if ($validator->fails())
            return response()->json(["message" => "Error occurred", 'errors' => $validator->errors()], 403);

        $user = User::where(array_only($data, ['email', 'phone']))
            ->with(UserRepository::LoginUserRelations)->first();
        if (!($user && Hash::check($data['password'], $user->password)))
            return response()->json(["message" => "Invalid user credentials"], 403);
        $token = $user->createToken('authToken');
        $device = $this->userAction->userDevice($request, $user, $token);
        $user['token'] = $token->plainTextToken;
        $user['device'] = $device;
        return response()->json($user);
    }
}
