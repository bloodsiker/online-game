                        <div class="form-group">
                            <label class="col-form-label">Откуда (from_location_id)</label>
                            <select class="gate-location-select form-control" name="from_location_id" required>
                                @if($gate?->fromLocation)
                                    <option value="{{ $gate->fromLocation->id }}" selected>[{{ $gate->fromLocation->id }}] {{ $gate->fromLocation->name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Куда (to_location_id)</label>
                            <select class="gate-location-select form-control" name="to_location_id" required>
                                @if($gate?->toLocation)
                                    <option value="{{ $gate->toLocation->id }}" selected>[{{ $gate->toLocation->id }}] {{ $gate->toLocation->name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Ключ-предмет (share_item_id)</label>
                            <select id="gate-item-select" class="form-control" name="share_item_id" required>
                                @if($gate?->shareItem)
                                    <option value="{{ $gate->shareItem->id }}" selected>[{{ $gate->shareItem->id }}] {{ $gate->shareItem->name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Режим</label>
                            <select class="form-control" name="mode">
                                <option value="presence_pass" @selected(($gate->mode ?? 'presence_pass') === 'presence_pass')>Ключ в сумке — кнопка на локации</option>
                                <option value="teleport_use" @selected(($gate->mode ?? '') === 'teleport_use')>Ключ используется из инвентаря — телепорт</option>
                            </select>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="consume_item" name="consume_item" value="1" @checked($gate?->consume_item)>
                            <label class="form-check-label" for="consume_item">Ключ расходуется при использовании (для режима "телепорт")</label>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Подпись кнопки (только для режима "ключ в сумке")</label>
                            <input type="text" class="form-control" name="button_label" value="{{ $gate->button_label ?? '' }}" placeholder="Пройти сквозь решетку">
                        </div>