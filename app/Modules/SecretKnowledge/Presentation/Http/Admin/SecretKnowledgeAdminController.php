<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\BossKnowledgeManual;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\SecretKnowledgeSettings;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SecretKnowledgeAdminController extends Controller
{
    public function index(): View
    {
        return view('secret-knowledge::admin', [
            'settings' => SecretKnowledgeSettings::query()->with(['npc', 'bundle'])->first(),
            'manuals' => BossKnowledgeManual::query()->with(['manual', 'monster', 'bossPart'])->orderBy('required_knowledge')->get(),
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'npc_id' => ['required', 'integer', 'exists:npcs,id'],
            'bundle_share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'analysis_success_chance' => ['required', 'integer', 'min:0', 'max:100'],
            'knowledge_gain_min' => ['required', 'integer', 'min:1', 'max:1000000'],
            'knowledge_gain_max' => ['required', 'integer', 'min:1', 'gte:knowledge_gain_min', 'max:1000000'],
        ]);
        $settings = SecretKnowledgeSettings::query()->first() ?? new SecretKnowledgeSettings;
        $settings->fill($data + ['is_active' => $request->boolean('is_active')]);
        $settings->save();

        return redirect()->route('admin.secret_knowledge')->with('success', 'Настройки тайных знаний сохранены.');
    }

    public function saveManual(Request $request, ?BossKnowledgeManual $manual = null): RedirectResponse
    {
        $data = $request->validate([
            'manual_share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'monster_id' => ['required', 'integer', 'exists:monsters,id'],
            'required_knowledge' => ['required', 'integer', 'min:0', 'max:100000000'],
            'boss_part_share_item_id' => ['required', 'integer', 'exists:share_items,id'],
        ]);
        abort_unless(ShareItem::query()->whereKey($data['manual_share_item_id'])->where('type', ShareItemType::BOSS_MANUAL)->exists(), 422, 'Предмет пособия должен иметь тип «Пособие о боссе».');
        abort_unless(ShareItem::query()->whereKey($data['boss_part_share_item_id'])->where('type', ShareItemType::QUEST)->exists(), 422, 'Часть босса должна иметь тип «Квест».');
        abort_unless(Monster::query()->whereKey($data['monster_id'])->where('is_boss', true)->exists(), 422, 'Для пособия нужно выбрать монстра с признаком босса.');
        $manual ??= new BossKnowledgeManual;
        $manual->fill($data + ['is_active' => $request->boolean('is_active')]);
        $manual->save();

        return redirect()->route('admin.secret_knowledge')->with('success', 'Пособие босса сохранено.');
    }

    public function deleteManual(BossKnowledgeManual $manual): RedirectResponse
    {
        $manual->delete();

        return redirect()->route('admin.secret_knowledge')->with('success', 'Связь пособия удалена.');
    }
}
