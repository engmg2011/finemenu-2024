<?php

namespace App\Http\Controllers;


use App\Actions\MediaAction;
use App\Http\Resources\DataResource;
use App\Jobs\UploadMenuQueue;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
        $user_id = auth('sanctum')->user()->id;
        request()->validate(['file' => 'required|file']);
        $file = $this->request->file('file');
        $file_type = $file->getMimeType();

        $file_name = rand(1000, 10000) . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).".".$file->clientExtension();
        $savePath = $user_id . '/';
        $fixFileName = str_replace(' ', '_', $file_name);
        $fixFileName = preg_replace('/\s+/', '_', $fixFileName);
        // The array contains: regular space, no-break space (U+00A0), narrow no-break space (U+202F)
        $fixFileName = str_replace([' ', ' ', ' '], '_', $fixFileName);
        $storagePath = "storage/" . $savePath . $fixFileName;
        if ($this->request->headers->has('convert-item')) {
            $fullPath = $this->request->header('full-path');
            $myFile = [
                'fullPath' => $fullPath,
                'uploadedFilePath' => $storagePath,
                'fileType' => $file_type
            ];
            $user = [
                'userId' => auth('sanctum')->user()->id,
                'menuId' => request()->header('menu-id'),
                'locale' => request()->header('locale')
            ];

            //UploadMenuQueue::dispatch($myFile, $user);
            $this->action->smartMenuUploader($myFile, $user);
        }

        $this->action->uploadMedia($file, $fixFileName, "public/" . $savePath);

        return response()->json([
            'file' => url($storagePath),
            'item' => $item ?? null,
            'media' => $media ?? null
        ]);
    }


    public function itemMediaSort(Request $request, $businessId, $itemId)
    {
        $request->validate([
           'sortedIds' => 'array',
        ]);
        $data = $request->all();
        $mediaIds = $data['sortedIds'];
        $dbMediaIds = Media::where('mediable_id', $itemId)
            ->whereIn('id',$mediaIds)->pluck('id');
        if(count($dbMediaIds) !== count($mediaIds)) {
            abort(400,'Wrong data');
        }
        return response()->json($this->action->sort($data));
    }
}
