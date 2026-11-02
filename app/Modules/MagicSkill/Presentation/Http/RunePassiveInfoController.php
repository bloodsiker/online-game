<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Share\Domain\Enums\CombatPassiveType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RunePassiveInfoController extends Controller
{
    public function __invoke(Request $request, CombatPassiveType $type): View
    {
        $data = $request->validate(['value' => ['required', 'integer', 'min:0', 'max:100000']]);

        // Rune passives are enum-based effects, not learned MagicSkill rows.
        // Adapt their read-only presentation to the shared skill info template.
        $skill = new MagicSkill([
            'name' => $type->label(),
            'description' => $type->description((int) $data['value']),
            'image' => $type->iconUrl(),
            'type' => 'utility',
            'is_passive' => true,
            'effects' => [],
        ]);
        $skill->setRelation('requirements', new Collection);
        $skill->setRelation('skillEffects', new Collection);

        return view('magic-skill::info', ['skill' => $skill, 'runePassiveInfo' => true]);
    }
}
