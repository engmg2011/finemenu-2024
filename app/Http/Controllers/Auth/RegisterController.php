<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SubscriptionAction;
use App\Constants\RolesConstants;
use App\Http\Controllers\Controller;
use App\Models\InitRegister;
use App\Models\IpTries;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repository\Eloquent\BusinessRepository;
use App\Repository\Eloquent\PermissionRepository;
use App\Repository\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function __construct(private BusinessRepository      $businessRepository,
                                private UserRepositoryInterface $userRepository,
                                private PermissionRepository    $permissionRepository,
                                private SubscriptionAction      $subscriptionAction)
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
     * @return bool
     */
    public function IpCanRegister(): bool
    {
        // TODO :: set times = 3
        $triesAvailable = 30;
        $tried = IpTries::where('ip', '=', $_SERVER['REMOTE_ADDR'])
            ->where('created_at', '>', Carbon::now()->subMinutes(15))->first();
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

        $searchArray = (isset($data['phone']) && $data['phone'] !== "") ?
            array_only($data, ['phone']) : array_only($data, ['email']);

        $codeData = InitRegister::where($searchArray)
            ->where('created_at', '>', Carbon::now()->subMinutes(15))
            ->first();
        if (is_null($codeData)) {
            $codeData = InitRegister::create($searchArray +
                [
                    'phone' => $data['phone'] ?? "",
                    'email' => $data['email'] ?? "",
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
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'Too many tries, try again later.'], 400);

        if (!$this->sendCodeProcess($data))
            return response()->json(['message' => 'Too many code tries, try again later.'], 400);

        return response()->json(["message" => "Code sent, Please enter the code you have received"]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = $this->resetValidator($data);

        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'Too many tries, try again later.'], 400);

        if (!$this->sendCodeProcess($data))
            return response()->json(['message' => 'Too many code tries, try again later.'], 400);

        return response()->json(["message" => "Code sent, Please enter the code you have received"]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function validateCode(Request $request, $reset = false): JsonResponse
    {
        $data = $request->all();
        $validator = $reset ? $this->resetValidator($data, ['code' => ['required']]) :
            $this->validator($data, ['code' => ['required']]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        $isValidCode = InitRegister::where(function ($query) use ($data) {
                if (isset($data['phone']) && $data['phone'] !== "")
                    return $query->where('phone', $data['phone']);
                if (isset($data['email']) && $data['email'] !== "")
                    return $query->where('email', $data['email']);
            })
            ->where('created_at', '>', Carbon::now()->subMinutes(15))
            ->where('code', $data['code'])->first();
        if (!$isValidCode)
            return response()->json(['message' => 'Wrong code, try again'], 400);
        if ($reset) {
            $user = User::where(array_only($data, ['email', 'phone']))->with('settings')->first();
            $token = $user->createToken('authToken');
            $device = $this->userRepository->userDevice($request, $user, $token);
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
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        if (!$this->IpCanRegister())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        $isValidCode = InitRegister::where(function ($query) use ($data) {
            return $query->where('phone', $data['phone'])
                ->orWhere('email', $data['email']);
        })
            ->where('created_at', '>', Carbon::now()->subMinutes(15))
            ->where('code', $data['code'])->first();

        if (!$isValidCode)
            return response()->json(['message' => 'Wrong code, try again'], 400);

        $data['type'] = "";
        $data['email_verified_at'] = Carbon::now();

        if (!isset($data['email']) || empty($data['email']))
            $data['email'] = $data['phone'] . '@' . env('APP_DOMAIN');

        if (isset($data['businessName']) && isset($data['businessType'])) {
            $data['dashboard_access'] = true;
            $data['is_employee'] = true;
        }

        // Create user && assign general role
        $user = $this->userRepository->createModel($data);
        $token = $user->createToken('API Token');

        if (isset($data['businessName']) && isset($data['businessType'])) {
            $user->assignRole(RolesConstants::BUSINESS_OWNER);
            $this->businessRepository->registerNewOwner($request, $user);
        }

        // Create subscription
        // $this->createSubscription($user);

        $device = $this->userRepository->userDevice($request, $user, $token);
        $user['device'] = $device;

        // TODO:: Notify user on his accounts
        return response()->json($user->toArray() + ['token' => $token->plainTextToken]);
    }

}
