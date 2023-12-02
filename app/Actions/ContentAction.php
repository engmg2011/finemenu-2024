<?php


namespace App\Actions;

use App\Models\Content;
use App\Repository\Eloquent\ContentRepository;
use Illuminate\Database\Eloquent\Model;

class ContentAction
{

    public function __construct(private ContentRepository $repository,
                                private LocaleAction      $localeAction)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'parent_id', 'contentable_id', 'contentable_type']);
    }

    public function create(array $data)
    {
        $data['user_id'] = request()->get('user_id') ?? auth('api')->user()->id;
        $model = $this->repository->create($this->process($data));
        $this->localeAction->createLocale($model, $data['locales']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeAction->updateLocales($model, $data['locales']);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return $this->repository->list();
    }

    public function get(int $id)
    {
        return Content::with(['locales', 'children.locales', 'children.children.locales'])->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->repository->delete($id);
    }
}
