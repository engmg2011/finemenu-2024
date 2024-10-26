<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SubscriptionAction;
use App\Actions\UserAction;
use App\Constants\RolesConstants;
use App\Http\Controllers\Controller;
use App\Models\InitRegister;
use App\Models\IpTries;
use App\Models\Package;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repository\Eloquent\BusinessRepository;
use App\Repository\Eloquent\PermissionRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isEmpty;

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
    public function __construct(private BusinessRepository   $businessRepository,
                                private UserAction           $userAction,
                                private PermissionRepository $permissionRepository,
                                private SubscriptionAction   $subscriptionAction)
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, array $extraValidation = []): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $extraValidation + [
                'email' => ['string', 'email', 'max:255', 'unique:users'],
                'phone' => ['string', 'min:8', 'max:15', 'unique:users'],
                'phone_required' => Rule::requiredIf(fn() => !isset($data['email']) && !isset($data['phone'])),
            ], [
            'phone_required' => "phone or email required"
        ]);
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function resetValidator(array $data, array $extraValidation = []): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $extraValidation + [
                'email' => ['string', 'email', 'max:255'],
                'phone' => ['string', 'min:8', 'max:15'],
                'phone_required' => Rule::requiredIf(fn() => !isset($data['email']) && !isset($data['phone'])),
            ], [
            'phone_required' => "phone or email required"
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
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

    /**
     * @return bool
     */
    public function IpCanRegister(): bool
    {
        // TODO :: set times = 3
        $triesAvailable = 30;
        $tried = IpTries::where('ip', '=', $_SERVER['REMOTE_ADDR'])->where('created_at', '>', Carbon::now()->addHours(-1))->first();
        if (is_null($tried))
            $tried = IpTries::create(['ip' => $_SERVER['REMOTE_ADDR'], 'tries' => 0]);
        if ($tried->tries < $triesAvailable)
            $tried->update(['tries' => $tried->tries + 1]);
        return $tried->tries < $triesAvailable;
    }

    /**
     * @param $data
     * @return void
     */
    public function sendCodeProcess($data): bool
    {
        // TODO:: create a random code
        $randomCode = '123456';

        // TODO :: set $available_code_tries = 3
        $available_code_tries = 30;

        $searchArray = isset($data['phone']) && !isEmpty($data['phone']) ? array_only($data, ['phone']) : array_only($data, ['email']);
        $codeData = InitRegister::where($searchArray)
            ->where('created_at', '>', Carbon::now()->subHour())
            ->first();
        if (is_null($codeData)) {
            $codeData = InitRegister::create($searchArray +
                [
                    'tries_count' => 0,
                    'code' => $randomCode,
                    'created_at' => Carbon::now()
                ]);
        }
        $tries = $codeData->tries_count;
        if ($tries >= $available_code_tries)
            return false;

        // TODO:: send the random code

        $codeData->tries_count = $tries + 1;
        $codeData->code = $randomCode;
        $codeData->save();
        return true;
    }

    /**
     * This method to ve
     * @param Request $request
     * @return JsonResponse
     */
    public function sendCode(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = $this->validator($data);

        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'Too many tries, try again later.'], 403);

        if (!$this->sendCodeProcess($data))
            return response()->json(['message' => 'Too many code tries, try again later.'], 403);

        return response()->json(["message" => "Code sent, Please enter the code you have received"]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = $this->resetValidator($data);

        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'Too many tries, try again later.'], 403);

        if (!$this->sendCodeProcess($data))
            return response()->json(['message' => 'Too many code tries, try again later.'], 403);

        return response()->json(["message" => "Code sent, Please enter the code you have received"]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function validateCode(Request $request, $reset = false): JsonResponse
    {
        $data = $request->all();
        $validator = $reset ? $this->resetValidator($data, ['code' => ['required']]) : $this->validator($data, ['code' => ['required']]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        $searchArray = isset($data['phone']) && !isEmpty($data['phone']) ? array_only($data, ['phone']) : array_only($data, ['email']);
        $isValidCode = InitRegister::where($searchArray)
            ->where('created_at', '>', Carbon::now()->subHour())
            ->where('code', $data['code'])->first();
        if (!$isValidCode)
            return response()->json(['message' => 'Wrong code, try again'], 403);
        if ($reset) {
            $user = User::where(array_only($data, ['email', 'phone']))->with('settings')->first();
            $token = $user->createToken('authToken');
            $device = $this->userAction->userDevice($request, $user, $token);
            $user['token'] = $token->plainTextToken;
            $user['device'] = $device;
            return response()->json($user);
        }
        return response()->json(['message' => 'success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        return $this->validateCode($request, true);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = $this->validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'password' => 'required|confirmed|min:8',
            'code' => 'required'
        ]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        $searchArray = isset($data['phone']) && !isEmpty($data['phone']) ? array_only($data, ['phone']) : array_only($data, ['email']);
        $isValidCode = InitRegister::where($searchArray)
            ->where('created_at', '>', Carbon::now()->subHour())
            ->where('code', $data['code'])->first();

        if (!$isValidCode)
            return response()->json(['message' => 'Wrong code, try again'], 403);

        $data['password'] = bcrypt($request->password);
        $data['type'] = "";
        $data['email_verified_at'] = Carbon::now();

        // Create user && assign general role
        $user = $this->userAction->create($data);
        $token = $user->createToken('API Token')->plainTextToken;

        if (isset($data['businessName']) && isset($data['businessType'])) {
            $user->assignRole(RolesConstants::BUSINESS_OWNER);
            $this->businessRepository->registerNewOwner($request, $user);
        }

        // Create subscription
        // $this->createSubscription($user);

        // TODO:: Notify user on his accounts
        return response()->json($user->toArray() + ['token' => $token]);
    }

}
