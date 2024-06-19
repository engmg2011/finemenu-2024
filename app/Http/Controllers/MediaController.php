<?php

namespace App\Http\Controllers;


use App\Actions\MediaAction;
use App\Http\Resources\DataResource;
use App\Jobs\UploadMenuQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MediaController extends Controller
{

    public function __construct(private Request $request, private MediaAction $action)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->action->list());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->action->create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->action->get($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return \response()->json($this->action->update($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->action->delete($id));
    }


    public function postUpload()
    {
        $user_id = auth('api')->user()->id;
        request()->validate(['file' => 'required|file']);
        $file = $this->request->file('file');
        $file_type = $file->getMimeType();

        $file_name = rand(1000, 10000) . '_' . $file->getClientOriginalName();
        $savePath = $user_id . '/';
        $uploadedFile = $savePath . $file_name;
        $storagePath = "storage/" . $uploadedFile;
        if ($this->request->headers->has('convert-item')) {
            $fullPath = $this->request->header('full-path');

            $myFile = [
                'fullPath' => $fullPath,
                'uploadedFilePath' => $storagePath,
                'fileType' => $file_type
            ];
            $user = [
                'userId' => auth('api')->user()->id,
                'restaurantId' => request()->header('restaurant-id'),
                'menuId' => request()->header('menu-id'),
                'locale' => request()->header('locale')
            ];

            UploadMenuQueue::dispatch( $myFile,$user);
        }

        $this->action->uploadMedia($file, $file_name, "public/" . $savePath);

        return response()->json([
            'file' => url($storagePath),
            'item' => $item ?? null,
            'media' => $media ?? null
        ]);
    }
}
