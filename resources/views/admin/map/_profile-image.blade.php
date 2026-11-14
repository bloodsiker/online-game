<div class="form-group">
    <label class="col-form-label">Картинка карты в публичном профиле</label>
    @if($map?->profile_image)
        <div class="mb-2"><img src="{{ $map->profile_image }}" alt="" style="width:400px;max-width:100%;aspect-ratio:5/2;object-fit:cover;"></div>
    @endif
    <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp">
    <small class="text-muted">Панорамное изображение, рекомендуемый формат 5:2 (например, 600×240 px). JPG, PNG или WebP, до 5 МБ.</small>
    @error('profile_image')<div class="text-danger">{{ $message }}</div>@enderror
    @if($map?->getRawOriginal('profile_image'))
        <label class="d-block mt-2"><input type="checkbox" name="delete_profile_image" value="1"> Удалить картинку профиля</label>
    @endif
</div>
