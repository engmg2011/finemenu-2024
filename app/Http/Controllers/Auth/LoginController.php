<?php

namespace App\Http\Controllers\Auth;

use App\Constants\RolesConstants;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repository\Eloquent\UserRepository;
use App\Repository\UserRepositoryInterface;
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
    public function __construct( private UserRepositoryInterface $userRepository)
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
            return response()->json(["message" => "Error occurred", 'errors' => $validator->errors()], 400);

        $user = User::where(array_only($data, ['email', 'phone']))
            ->with(UserRepository::LoginUserRelations)->first();

        if (!($user && Hash::check($data['password'], $user->password)))
            return response()->json(["message" => "Invalid user credentials"], 400);

        if(!$user->hasRole(RolesConstants::BUSINESS_OWNER)){
            if ($user && $request->input('dashboard') === true){
                if( count($user->business) === 0){
                    if(!$user->dashboard_access)
                        return response()->json(["message" => "User is not allowed to login"], 400);

                    if (!is_array($user->control) || count($user->control) === 0){
                        return response()->json(["message" => "User has no control"], 400);
                    }
                }
            }
        }

        $token = $user->createToken('Login Token');
        $device = $this->userRepository->userDevice($request, $user, $token);
        $user['token'] = $token->plainTextToken;
        $user['device'] = $device;
        return response()->json($user);
    }
}
