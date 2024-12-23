<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\BookmarkRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookmarksController extends Controller
{

    public function __construct(protected BookmarkRepositoryInterface $repository)
    {
    }


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
        return \response()->json($this->repository->create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->get($id));
    }

    public function userBookmarks(Request $request)
    {
        $businessId = $request->route('businessId');
        $branchId = $request->route('branchId');
        return DataResource::collection($this->repository->userBookmarks($businessId, $branchId));
    }

    public function syncBookmarks(Request $request)
    {
        $businessId = $request->route('businessId');
        $branchId = $request->route('branchId');
        $bookmarks = $request->get('item_ids');
        return DataResource::collection($this->repository->syncBookmarks($bookmarks, $businessId, $branchId));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        return \response()->json($this->repository->update(\request()->route('id'), $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
