<?php

namespace App\Repository\Eloquent;


use App\Constants\UserTypes;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Device;
use App\Models\LoginSession;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use App\Services\QrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    const LoginUserRelations = ['business.locales',
        'business.media',
        'business.branches.locales',
        'business.branches.media',
        'settings'];

    public function __construct(User                                  $model,
                                private readonly PermissionRepository $permissionRepository)
    {
        parent::__construct($model);
    }

    public function processUser(array $data)
    {
        return array_only($data, ['name', 'email', 'phone', 'currency',
            'password', 'email_verified_at', 'business_id', 'dashboard_access']);
    }

    public function createModel(array $data)
    {
        if ($data['password'])
            $data['password'] = bcrypt($data['password']);
        return $this->model->create($this->processUser($data));
    }

    public function search($businessId, array $data)
    {
        return User::where(function ($query) use ($businessId) {
                return $query->where('business_id', $businessId)
                    ->orWhereHas('business', function ($query) use ($businessId) {
                        $query->where('id', $businessId);
                    });
//                    ->orWhereNull('business_id');
            })
            ->where(function ($query) use ($data) {
                return $query->where('name', 'like', "%{$data['search']}%")
                    ->orWhere('email', 'like', "%{$data['search']}%")
                    ->orWhere('phone', 'like', "%{$data['search']}%");
            })
            ->paginate(5);
    }


    public function menu($user_id)
    {
        return User::with([
            'categories.locales', 'categories.media', 'categories.children.locales',
            'categories.children.media', 'categories.items.locales', 'categories.items.media', 'categories.items.prices.locales',
            'categories.children.items.locales', 'categories.children.items.media', 'categories.children.items.prices.locales',
            'items.locales', 'items.media', 'items.prices.locales'
        ])->find($user_id);
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return User::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function listModel($businessId)
    {
        return $this->model::where('business_id', $businessId)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return $this->model->find($id);
    }

    public function updateModel($id, array $data): \Illuminate\Database\Eloquent\Builder|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {

        if (isset($data['password']) && $data['password'] !== "")
            $data['password'] = bcrypt($data['password']);
        else
            unset($data['password']);

        $model = tap($this->model->find($id))
            ->update($this->processUser($data));
        return User::with('media')->find($model->id);
    }


    public function createLoginQr($businessId, $branchId)
    {
        // Generate a login token (for demonstration, use a random string)
        $token = bin2hex(random_bytes(16));

        LoginSession::create(['login_session' => $token,
            'valid_until' => Carbon::now()->addMinutes(15)]);

        // Generate the QR code with the login token
        $content = route('login.qr', ['token' => $token,
                'modelId' => $branchId,
                'type' => UserTypes::SUPERVISOR,
            ] + compact('businessId'));

        return (new QrService())->generateBase64QrCode($content);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getBranchSubUser($branchId, $userType): User
    {
        $branch = Branch::find($branchId);
        $userSlug = $branch->slug . '-' . $userType;
        $userEmail = $userSlug . '@menu-ai.net';
        for ($i = 0; $i < 100; $i++) {
            if (!User::where('email', $userEmail)->exists())
                break;
            $userEmail = $userSlug . '-' . rand(0, 100) . '@menu-ai.net';
        }
        $subUser = User::create(['email' => $userEmail,
            'name' => $userSlug,
            'type' => $userType,
            'password' => $this->generateRandomString(),
            'business_id' => $branch->business_id,
        ]);
        $subUser->assignRole($userType);
        $this->permissionRepository->createBranchPermission($branchId, $subUser);
        return $subUser;
    }

    /**
     * // Create a device for sub user
     * // if no device name sent : create device for this user and save device name
     * // if device name sent : unAuthorize the old device with the same device name
     * // save ip, last login, last sync, OS, ....
     * @param Request $request
     * @param $subUser
     * @param $token
     * @return Device
     */
    public function subUserDevice(Request $request, $subUser, $authToken, $branchSlug): Device
    {
        $branchId = \request()->route('modelId');
        $type = $request->input('type');

        $deviceName = $request->input('device_name');
        if (!$deviceName)
            $deviceName = $branchSlug . '-' . random_int(1000, 9999);

        return Device::updateOrCreate(['device_name' => $deviceName], [
            'token_id' => $authToken->accessToken->id,
            'user_id' => $subUser->id,
            'branch_id' => $branchId,
            'type' => $type,
            'device_name' => $deviceName,
            'os' => request()->header('User-Agent'),
            'onesignal_token' => request()->input('onesignal_token'),
            'version' => request()->header('App-Version'),
            'info' => request()->header('App-Info'),
            'last_active' => Carbon::now()
        ]);

    }

    /**
     * // Create a device for a user
     * // if no device name sent : create device for this user and save device name
     * // if device name sent : unAuthorize the old device with the same device name
     * // save ip, last login, last sync, OS, ....
     * @param Request $request
     * @param $subUser
     * @param $token
     * @return Device
     */
    public function userDevice(Request $request, $user, $authToken): Device
    {
        $deviceName = $request->input('device_name');
        if (!$deviceName)
            $deviceName = strtolower(str_replace(' ', '_', $user->name)) . '-' . random_int(1000, 9999);

        return Device::updateOrCreate(['device_name' => $deviceName], [
            'token_id' => $authToken->accessToken->id,
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'os' => request()->header('User-Agent'),
            'onesignal_token' => request()->input('onesignal_token'),
            'version' => request()->header('App-Version'),
            'info' => request()->header('App-Info'),
            'last_active' => Carbon::now()
        ]);

    }

    public function loginByQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'type' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails())
            return response()->json(['message' => 'Invalid QR code'], 401);

        $branchId = \request()->route('branchId');
        $token = $request->input('token');
        $type = $request->input('type');
        $loginSession = LoginSession::where('login_session', $token)
            ->where('valid_until', '<', Carbon::now())->first();
        // Validate the token (you can add your own validation logic here)
        if (!$loginSession)
            return response()->json(['message' => 'Invalid QR Login code'], 400);

        // get subUser or create  if no one exists
        // only one subUser with the same type under every owner
        if (!in_array($type, [UserTypes::KITCHEN, UserTypes::CASHIER,
            UserTypes::DRIVER, UserTypes::SUPERVISOR]))
            return response()->json(['message' => 'Invalid type'], 400);

        $subUser = $this->getBranchSubUser($branchId, $type);
        $authToken = $subUser->createToken('QR Auth Token');
        $subUser['token'] = $authToken->plainTextToken;

        $branch = Branch::find($branchId);
        $branchSlug = $branch->slug;
        $business = Business::with('locales', 'media', 'branches.locales', 'branches.media')->find($branch->business_id);

        $device = $this->subUserDevice($request, $subUser, $authToken, $branchSlug);
        return response()->json(compact('device', 'business') + [
                'user' => $subUser,
                'branch_slug' => $branchSlug,
                'message' => 'Logged in successfully',
            ]);

    }

    public function destroy($id): ?bool
    {
        return $this->find($id)?->delete();
    }


}
