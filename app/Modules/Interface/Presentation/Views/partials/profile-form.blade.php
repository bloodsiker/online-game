@php($profile = $profile ?? null)
<form class="profile-form" method="post" action="{{ route('settings.profile.update') }}">
    @csrf
    @if($errors->any())
        <div role="alert" style="color:#BA0000">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <table class="profile-table">
        <tbody>
        <tr>
            <th><label for="profile-name">{{ __('ui.real_name') }}</label></th>
            <td><div class="profile-input-wrap"><div class="profile-input-inner"><input id="profile-name" name="real_name" type="text" maxlength="32" autocomplete="given-name" value="{{ old('real_name', $profile?->real_name) }}"></div></div></td>
        </tr>
        <tr>
            <th>{{ __('ui.birth_date') }}</th>
            <td>
                <div class="profile-birthday">
                    <span class="profile-select-wrap"><select class="profile-select" name="birth_day" aria-label="{{ __('ui.birth_day') }}">
                        <option value="">--</option>
                        @for($day = 1; $day <= 31; $day++)
                            <option value="{{ $day }}" @selected((string) old('birth_day', $profile?->birth_date?->day) === (string) $day)>{{ str_pad((string) $day, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select></span>
                    <span class="profile-select-wrap"><select class="profile-select" name="birth_month" aria-label="{{ __('ui.birth_month') }}">
                        <option value="">--</option>
                        @foreach(__('ui.months') as $month)
                            <option value="{{ $loop->iteration }}" @selected((string) old('birth_month', $profile?->birth_date?->month) === (string) $loop->iteration)>{{ $month }}</option>
                        @endforeach
                    </select></span>
                    <span class="profile-select-wrap"><select class="profile-select" name="birth_year" aria-label="{{ __('ui.birth_year') }}">
                        <option value="">--</option>
                        @for($year = now()->year; $year >= now()->year - 100; $year--)
                            <option value="{{ $year }}" @selected((string) old('birth_year', $profile?->birth_date?->year) === (string) $year)>{{ $year }}</option>
                        @endfor
                    </select></span>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="profile-residence">{{ __('ui.residence') }}</label></th>
            <td>
                <div class="profile-input-wrap"><div class="profile-input-inner"><input id="profile-residence" name="residence" type="text" maxlength="100" autocomplete="address-level2" value="{{ old('residence', $profile?->residence) }}"></div></div>
            </td>
        </tr>
        <tr>
            <th><label for="profile-motto">{{ __('ui.motto') }}</label></th>
            <td><div class="profile-input-wrap"><div class="profile-input-inner"><input id="profile-motto" name="motto" type="text" maxlength="150" value="{{ old('motto', $profile?->motto) }}"></div></div></td>
        </tr>
        <tr>
            <th><label for="profile-about">{{ __('ui.about') }}</label></th>
            <td><textarea id="profile-about" name="about" class="profile-textarea" rows="10" maxlength="5000">{{ old('about', $profile?->about) }}</textarea></td>
        </tr>
        <tr>
            <td colspan="2" class="profile-actions">
                <span class="butt1 pointer"><span><input type="submit" value="{{ __('ui.save') }}"></span></span>
            </td>
        </tr>
        </tbody>
    </table>
</form>
