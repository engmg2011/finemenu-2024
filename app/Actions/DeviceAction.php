<?php


namespace App\Actions;

use App\Models\Device;
use App\Repository\Eloquent\DeviceRepository;
use Illuminate\Database\Eloquent\Model;

class DeviceAction
{
    private $repository;

    public function __construct(DeviceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function process(array $data): array
    {
        return array_only($data, ['token_id', 'device_name', 'player_id', 'last_sync', 'user_id', 'info']);
    }

    public function create(array $data)
    {
        return $this->repository->create($this->process($data));
    }

    public function update($id, array $data): Model
    {
        return tap($this->repository->find($id))
            ->update($this->process($data));
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Device::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Device::find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->repository->delete($id);
    }
}
