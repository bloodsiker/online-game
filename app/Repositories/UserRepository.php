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

    private function query()
    {
        return $this->model->query();
    }

    public function getQuery()
    {
        $query = $this->model->query();

        return $query->select(['users.*']);
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return parent::create($data);
    }

    public function update(array $data, $id)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $model = $this->getOneById($id);
        $model->update($data);

        return $model;
    }

    public function delete($id): void
    {
        $item = $this->model->find($id);

        $item->roles()->sync([]);

        $item->delete();
    }

    public function findByBxID($bxID)
    {
        return $this->model->query()->where('bx_id', '=', $bxID)->first();
    }

    public function search($term): void
    {
        $this->query()->where(function ($query) use ($term) {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%');
        })->get();
    }

    public function autocompletePagination(Request $request, $param = false)
    {
        $term = str_replace(['+', '-', '(', ')', '_'], '', $request->get('q'));
        $page = $request->get('page') ?: 1;
        $perPage = $request->get('per_page') ?: 20;

        $query = $this->query();

        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%');
        })->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    public function list($request)
    {
        $query = $this->query();

        return $query->limit(100)->get();
    }

    public function listTutors()
    {
        return $this->query()->where('role_id', Role::ROLE_ADMIN)->get();
    }

    public function listForAdmin(Request $request)
    {
        $query = $this->query();

        if ($request->get('fio')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('user_name', 'LIKE', '%'.$request->get('fio').'%')
                    ->orWhere('last_name', 'LIKE', '%'.$request->get('fio').'%')
                    ->orWhere('middle_name', 'LIKE', '%'.$request->get('fio').'%');
            });
        }

        if ($request->get('phone')) {
            $query->where('phone', 'LIKE', '%'.$request->get('phone').'%');
        }

        if ($request->get('email')) {
            $query->where('email', 'LIKE', '%'.$request->get('email').'%');
        }

        if ($request->get('disable')) {
            $query->where('disable', '=', $request->get('disable') === 'on' ? 0 : 1);
        }

        if ($request->get('training_format')) {
            $query->where('training_format', '=', $request->get('training_format'));
        }

        return $query->orderByDesc('id')->paginate(50);
    }
}
