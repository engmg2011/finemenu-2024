<?php

namespace App\Repository\Eloquent;

use App\Models\Bookmark;
use App\Repository\BookmarkRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BookmarkRepository extends BaseRepository implements BookmarkRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Bookmark $model
     */
    public function __construct(Bookmark $model) {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'item_id', 'branch_id', 'business_id']);
    }

    public function create(array $data): Model
    {
        $model = $this->model->create($this->process($data));
        return $model;
    }

    public function update($id, $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        return $model;
    }

    public function list()
    {
        return Bookmark::with('item')->where('user_id', auth()->id())
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function userBookmarks($businessId, $branchId)
    {
        $userId = auth('sanctum')->id();
        $userBranchItemsArr = [
            'user_id'=> $userId ,
            'business_id' => $businessId,
            'branch_id' => $branchId
        ];
        return Bookmark::with('item.locales', 'item.media',
            'item.prices', 'item.discounts', 'item.addons')
            ->where($userBranchItemsArr)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function syncBookmarks($bookmarks,$businessId, $branchId)
    {
        $userId = auth('sanctum')->id();
        $userBranchItemsArr = [
            'user_id'=> $userId ,
            'business_id' => $businessId,
            'branch_id' => $branchId
        ];
        $existingItems = Bookmark::where($userBranchItemsArr)
            ->whereIn('item_id', $bookmarks)->get();
        $existingKeys = $existingItems->pluck('item_id')->toArray();
        $newItems = collect($bookmarks)
            ->filter(fn($item) => !in_array($item, $existingKeys));
        $newBookmarks = collect();
        $newItems->map(function($itemId) use( $userBranchItemsArr,  &$newBookmarks) {
            $bookmark = $userBranchItemsArr;
            $bookmark['item_id'] = $itemId;
            $newBookmarks->push($bookmark);
        });
        Bookmark::insert($newBookmarks->toArray());

        // Remove not sent items in same branch
        Bookmark::where($userBranchItemsArr)
            ->whereNotIn('item_id', $bookmarks)->delete();

        return Bookmark::where($userBranchItemsArr)->get();
    }



    public function get(int $id)
    {
        return Bookmark::with('item')->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->delete($id);
    }

}
