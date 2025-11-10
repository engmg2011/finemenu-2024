<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Contact;
use App\Models\User;
use App\Repository\ContactRepositoryInterface;
use App\Repository\Eloquent\ContactRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    private $repository;

    public function __construct(ContactRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->repository->list());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->get($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return \response()->json($this->repository->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        return $this->repository->delete($id);
    }


    public function setUserContact(Request $request, $userId)
    {
        $this->repository = app(ContactRepository::class);
        $contacts = $request->get('contacts', []);

        foreach ($contacts as &$contact) {
            if (!isset($contact['value']) || empty($contact['value']) ||
                !isset($contact['media']) || empty($contact['media']) ||
                !isset($contact['key']) || empty($contact['key']) ) {
                abort('400', 'invalid data');
            }
        }

        foreach ($contacts as &$data) {
            $data['contactable_id'] = $userId;
            $data['contactable_type'] = User::class;

            if(isset($data['id'])) {
                $this->repository->updateModel($data['id'], $data);
            }
            else{
                $id = Contact::where('contactable_id', $userId)
                    ->where('contactable_type', User::class)
                    ->where('key', $data['key'])
                    ->first()?->id;
                if($id)
                    $this->repository->updateModel($id, $data);
                else
                    $this->repository->createModel($data);
            }
        }

        return \response()->json(User::find($userId)->contacts);

    }
}
