@extends('admin.layout.base')

@section('title')
    Создать монстра
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.monster.create') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row pb-3 pt-2">

                            <div class="col-lg-4">
                                <h6 class="text-muted mb-3">Основное</h6>
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Уровень</label>
                                    <input type="number" class="form-control" name="lvl" value="{{ old('lvl', 1) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Описание</label>
                                    <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Изображение</label>
                                    <div class="mb-1">
                                        <img id="monster-preview" src="" alt="" style="width:300px;height:300px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                    </div>
                                    <input type="file" class="form-control" name="image" id="monster-image" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Босс</label>
                                    <select class="form-control" name="is_boss">
                                        <option value="0" @selected(old('is_boss', '0') === '0')>Нет</option>
                                        <option value="1" @selected(old('is_boss') === '1')>Да</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <h6 class="text-muted mb-3">Боевые характеристики</h6>
                                <div class="form-group">
                                    <label class="col-form-label">HP</label>
                                    <input type="number" class="form-control" name="hp" value="{{ old('hp', 100) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Защита</label>
                                    <input type="number" class="form-control" name="armor" value="{{ old('armor', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Уворот (%)</label>
                                    <input type="number" class="form-control" name="dodge" value="{{ old('dodge', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Крит (%)</label>
                                    <input type="number" class="form-control" name="critical" value="{{ old('critical', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Мин. атака</label>
                                    <input type="number" step="0.01" class="form-control" name="min_dmg" value="{{ old('min_dmg', 1) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Макс. атака</label>
                                    <input type="number" step="0.01" class="form-control" name="max_dmg" value="{{ old('max_dmg', 1) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Тип атаки</label>
                                    <select class="form-control" name="attack_type">
                                        @foreach (\App\Modules\Monster\Domain\Enums\MonsterAttackType::cases() as $attackType)
                                            <option value="{{ $attackType->value }}" @selected(old('attack_type', 'physical') === $attackType->value)>{{ $attackType->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Магическая атака</label>
                                    <input type="number" min="0" class="form-control" name="magic_attack" value="{{ old('magic_attack', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Коэффициент магии</label>
                                    <input type="number" min="0" step="0.01" class="form-control" name="magic_power_coefficient" value="{{ old('magic_power_coefficient', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Магическое сопротивление</label>
                                    <input type="number" min="0" class="form-control" name="magic_resistance" value="{{ old('magic_resistance', 1) }}">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <h6 class="text-muted mb-3">Награда</h6>
                                <div class="form-group">
                                    <label class="col-form-label">Агрессивность (%)</label>
                                    <input type="number" class="form-control" name="aggression" value="{{ old('aggression', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Опыт</label>
                                    <input type="number" class="form-control" name="exp" value="{{ old('exp', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Мин. монет</label>
                                    <input type="number" class="form-control" name="min_money" value="{{ old('min_money', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Макс. монет</label>
                                    <input type="number" class="form-control" name="max_money" value="{{ old('max_money', 0) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Создать</button>
                                <a href="{{ route('admin.monsters') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.getElementById('monster-image').addEventListener('change', function () {
            const preview = document.getElementById('monster-preview');
            if (this.files[0]) {
                preview.src = URL.createObjectURL(this.files[0]);
                preview.style.display = 'block';
            }
        });
    </script>

@endsection
