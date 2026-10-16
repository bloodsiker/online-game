@extends('admin.layout.base')

@section('title')
    Раса: {{ $race->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.race.info', $race->id) }}" method="post">
                        {{ csrf_field() }}
                        @include('admin.partials.content-translations', ['prefix' => 'race-'.$race->id, 'content' => $race, 'fields' => ['name' => ['label' => 'Название']]])
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Сила</label>
                                    <input type="number" step="0.01" class="form-control" name="strength" value="{{ $race->strength }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Ловкость</label>
                                    <input type="number" step="0.01" class="form-control" name="agility" value="{{ $race->agility }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интуиция</label>
                                    <input type="number" step="0.01" class="form-control" name="intuition" value="{{ $race->intuition }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Мудрость</label>
                                    <input type="number" step="0.01" class="form-control" name="wisdom" value="{{ $race->wisdom }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интеллект</label>
                                    <input type="number" step="0.01" class="form-control" name="intelligence" value="{{ $race->intelligence }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Выносливость</label>
                                    <input type="number" step="0.01" class="form-control" name="endurance" value="{{ $race->endurance }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Своб. характеристики</label>
                                    <input type="number" class="form-control" name="free_stats" value="{{ $race->free_stats }}">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.race') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <section class="card">
                <div class="card-header"><h3 class="card-title">Аватары для регистрации</h3></div>
                <div class="card-body">
                    <p class="text-muted">Загруженные портреты появятся в выборе аватара при регистрации персонажа этой расы.</p>

                    @unless($avatarCatalogReady)
                        <div class="alert alert-warning mb-0">Каталог аватаров ещё не создан в базе. Для загрузки и удаления аватаров сначала нужно применить миграцию.</div>
                    @else

                    <form action="{{ route('admin.race.avatar.upload', $race) }}" method="post" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-2">
                                <label>Пол</label>
                                <select name="sex" class="form-control" required>
                                    <option value="male">Мужской</option>
                                    <option value="female">Женский</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Подпись</label>
                                <input type="text" name="name" class="form-control" maxlength="100" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Изображение</label>
                                <input type="file" name="image" class="form-control-file" accept="image/*" required>
                                <small class="form-text text-muted">До 5 МБ. Файл сохраняется в папку текущего месяца.</small>
                            </div>
                            <div class="form-group col-md-1">
                                <label>Порядок</label>
                                <input type="number" name="sort_order" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group col-md-2">
                                <button type="submit" class="btn btn-primary">Загрузить</button>
                            </div>
                        </div>
                    </form>

                    @if($avatars->isEmpty())
                        <div class="alert alert-info mb-0">Для этой расы аватары ещё не добавлены.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr><th>Изображение</th><th>Подпись</th><th>Пол</th><th>Порядок</th><th></th></tr>
                                </thead>
                                <tbody>
                                @foreach($avatars as $avatar)
                                    <tr>
                                        <td><img src="{{ $avatar->image_url }}" alt="{{ $avatar->name }}" style="width:80px;height:80px;object-fit:cover"></td>
                                        <td>{{ $avatar->name }}</td>
                                        <td>
                                            <form action="{{ route('admin.race.avatar.update', [$race, $avatar]) }}" method="post" class="d-flex align-items-center">
                                                @csrf
                                                @method('PATCH')
                                                <select name="sex" class="form-control form-control-sm mr-2" aria-label="Пол аватара">
                                                    <option value="male" @selected($avatar->sex === 'male')>Мужской</option>
                                                    <option value="female" @selected($avatar->sex === 'female')>Женский</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                                            </form>
                                        </td>
                                        <td>{{ $avatar->sort_order }}</td>
                                        <td>
                                            <form action="{{ route('admin.race.avatar.delete', [$race, $avatar]) }}" method="post" onsubmit="return confirm('Удалить этот аватар?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @endunless
                </div>
            </section>
        </div>
    </div>

@endsection
