<?php

namespace App\Http\Controllers\Auth;

use App\Actions\HotelAction;
use App\Actions\PermissionAction;
use App\Actions\RestaurantAction;
use App\Actions\SubscriptionAction;
use App\Actions\UserAction;
use App\Constants\RolesConstants;
use App\Http\Controllers\Controller;
use App\Models\IpTries;
use App\Models\Package;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private RestaurantAction   $restaurantAction,
                                private HotelAction        $hotelAction,
                                private UserAction         $userAction,
                                private PermissionAction   $permissionAction,
                                private SubscriptionAction $subscriptionAction)
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [ 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => [ 'string', 'min:8', 'max:15', 'unique:users'],
            'phone_required' => Rule::requiredIf(fn () => !isset($data['email']) && !isset($data['phone'])),
        ], [
            'phone_required' => "phone or email required"
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }


    public function canRegister(): bool
    {
        $triesAvailable = 10;
        $tried_times = IpTries::where('ip', '=', "")->where('created_at' ,'>', Carbon::yesterday())->first()?->tries || 0;
        return $tried_times < $triesAvailable;
    }

    public function saveIpTry(): void
    {
        $lastTry = IpTries::where('ip', '=', $_SERVER['REMOTE_ADDR'])->where('created_at' ,'>',Carbon::yesterday())->get()->first();
        if(!$lastTry)
            $lastTry = IpTries::create(['ip' => $_SERVER['REMOTE_ADDR'] , 'tries' => 1]);
        $lastTry->tries = $lastTry->tries++;
        $lastTry->save();
    }

    public function register(Request $request)
    {
        $data = $request->all();
        $validator = $this->validator($data);
//        return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if(!$this->canRegister())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        $data['password'] = bcrypt($request->password);
        $data['type'] = RolesConstants::OWNER;

        // Create user && assign general role
        $user = $this->userAction->create($data);
        $user->assignRole(RolesConstants::OWNER);
        $token = $user->createToken('API Token')->accessToken;

        // Create restaurant and give permission
        $businessData = ['user_id' => $user->id, 'creator_id' => $user->id,
            'name' => $request->businessName , 'slug'=> slug($request->businessName)];
        $restaurant = $this->restaurantAction->createModel($businessData);
        $this->permissionAction->setRestaurantOwnerPermissions($user->id, $restaurant->id);
/*
        // Create hotel and give permission
        $hotel = $this->hotelAction->createModel($businessData);
        $this->permissionAction->setHotelOwnerPermissions($user->id, $hotel->id);

        // Create subscription and assign trial package
        $package = Package::where('slug', 'trial')->first();
        $expiry = (new Carbon())->addDays($package->days)->format('Y-m-d H:i:s');
        $this->subscriptionAction->create(['creator_id' => $user->id, 'user_id' => $user->id,
            'package_id' => $package->id, 'from' => Carbon::now(), 'to' => $expiry]);

*/

        // TODO:: Notify user on his accounts

        return response(['user' => $user, 'access_token' => $token]);
    }

}
