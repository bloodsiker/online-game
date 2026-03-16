<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository extends AbstractRepository
{

    public function getModelClass(): string
    {
        return User::class;
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query()->select(['users.*']);
    }

    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        $data['password'] = Hash::make($data['password']);

        return parent::create($data);
    }

    public function update(array $data, int|string $id): \Illuminate\Database\Eloquent\Model
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function delete(int|string $id): void
    {
        $item = $this->model->findOrFail($id);

        $item->roles()->sync([]);

        $item->delete();
    }

    public function search($term): void
    {
        $this->getQuery()->where(function ($query) use ($term) {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%');
        })->get();
    }

    public function autocompletePagination(Request $request, $param = false)
    {
        $term = str_replace(['+', '-', '(', ')', '_'], '', $request->get('q'));
        $page = $request->get('page') ?: 1;
        $perPage = $request->get('per_page') ?: 20;

        $query = $this->getQuery();

        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%');
        })->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    public function list($request)
    {
        $query = $this->getQuery();

        return $query->limit(100)->get();
    }

    public function listTutors()
    {
        return $this->getQuery()->where('role_id', Role::ROLE_ADMIN)->get();
    }

    public function listForAdmin(Request $request)
    {
        $query = $this->getQuery();

        if ($request->get('fio')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('user_name', 'LIKE', '%'.$request->get('fio').'%')
                    ->orWhere('last_name', 'LIKE', '%'.$request->get('fio').'%')
                    ->orWhere('middle_name', 'LIKE', '%'.$request->get('fio').'%');
            });
        }

        if ($request->get('email')) {
            $query->where('email', 'LIKE', '%'.$request->get('email').'%');
        }

        if ($request->get('disable')) {
            $query->where('disable', '=', $request->get('disable') === 'on' ? 0 : 1);
        }

        return $query->orderByDesc('id')->paginate(50);
    }
}
