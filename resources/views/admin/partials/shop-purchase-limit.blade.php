@php($offer = $offer ?? null)
<div class="shop-purchase-limit-fields">
    <label>Лимит на игрока, шт. (0 — без ограничения)</label>
    <input class="form-control form-control-sm" type="number" name="purchase_limit_quantity" min="0" max="1000000"
           value="{{ $offer?->purchase_limit_quantity ?? 0 }}" @if(!empty($formId)) form="{{ $formId }}" @endif>
    <label class="mt-2">Период, дней</label>
    <input class="form-control form-control-sm" type="number" name="purchase_limit_period_days" min="0" max="36500" step="any"
           value="{{ ($offer?->purchase_limit_period_seconds ?? 0) / 86400 }}" @if(!empty($formId)) form="{{ $formId }}" @endif>
    <small class="text-muted">Отсчёт начинается с первой покупки. Количество можно покупать частями.</small>
</div>
