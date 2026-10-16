<?php

declare(strict_types=1);

// Temporary import tool. Never overwrites Russian text or an existing Ukrainian translation.
// Usage: php scripts/translate-items-uk.php export|translate|preview|apply [work-directory] [entity]

use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventStage;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceMedal;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceShopSection;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossPhase;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueOption;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestDialogue;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Trade\Infrastructure\Persistence\Models\TradePost;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = $argv[1] ?? 'preview';
$entity = $argv[3] ?? 'items';
$models = ['items' => ShareItem::class, 'maps' => Map::class, 'locations' => Location::class, 'structures' => Structure::class, 'share_actions' => ShareAction::class, 'share_structure_categories' => ShareStructureCategory::class, 'dungeons' => Dungeon::class, 'dungeon_stages' => DungeonStage::class, 'boss_phases' => BossPhase::class, 'world_events' => WorldEvent::class, 'world_event_stages' => WorldEventStage::class, 'event_activities' => EventActivity::class, 'clan_skill_definitions' => ClanSkillDefinition::class, 'news_posts' => NewsPost::class, 'influence_shop_sections' => InfluenceShopSection::class, 'trade_posts' => TradePost::class, 'library_categories' => LibraryCategory::class, 'library_articles' => LibraryArticle::class, 'injury_types' => InjuryType::class, 'map_influence_levels' => MapInfluenceLevel::class, 'races' => Race::class, 'monsters' => Monster::class, 'npcs' => Npc::class, 'skills' => Skill::class, 'magic_skills' => MagicSkill::class, 'effects' => Effect::class, 'reputations' => Reputation::class, 'reputation_tiers' => ReputationTier::class, 'influence_medals' => InfluenceMedal::class, 'quests' => Quest::class, 'quest_dialogues' => QuestDialogue::class, 'quest_stages' => QuestStage::class, 'quest_objectives' => QuestObjective::class, 'npc_dialogue_nodes' => NpcDialogueNode::class, 'npc_dialogue_options' => NpcDialogueOption::class];
if (! isset($models[$entity])) {
    fwrite(STDERR, 'Entities: '.implode(', ', array_keys($models))."\n");
    exit(1);
}
$modelClass = $models[$entity];
$contentFields = (new $modelClass)->getTranslatableAttributes();
$directory = $argv[2] ?? storage_path('app/translations/'.$entity.'-ru-uk-'.date('Y-m-d'));
if (! in_array($mode, ['export', 'translate', 'preview', 'apply'], true)) {
    fwrite(STDERR, "Modes: export, translate, preview, apply\n");
    exit(1);
}

function writeJson(string $path, mixed $data): void
{
    $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    if (file_put_contents($path.'.tmp', $encoded, LOCK_EX) === false || ! rename($path.'.tmp', $path)) {
        throw new RuntimeException('Could not save '.$path);
    }
}

function readJson(string $path): array
{
    if (! is_file($path)) {
        throw new RuntimeException('Missing '.$path);
    }

    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
}

function translateText(string $text): string
{
    if (preg_match('/[А-Яа-яЁё]/u', $text) !== 1) {
        return $text;
    }

    // Split long paragraphs before GET so the query stays below public endpoint limits.
    $chunks = [];
    $current = '';
    foreach (preg_split('/(?<=\s)/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $word) {
        if ($current !== '' && mb_strlen($current.$word) > 700) {
            $chunks[] = $current;
            $current = '';
        }
        $current .= $word;
    }
    if ($current !== '') {
        $chunks[] = $current;
    }
    if (count($chunks) > 1) {
        $translated = '';
        foreach ($chunks as $chunk) {
            $translated .= translateText($chunk);
        }

        return $translated;
    }

    // The public Google Translate web endpoint has no SLA. Requests are paced and resumable.
    $response = Http::timeout(40)->retry(3, 2000)->get('https://translate.googleapis.com/translate_a/single', [
        'client' => 'gtx', 'sl' => 'ru', 'tl' => 'uk', 'dt' => 't', 'q' => $text,
    ])->throw()->json();
    $translated = '';
    foreach ($response[0] ?? [] as $segment) {
        $translated .= $segment[0] ?? '';
    }
    if (trim($translated) === '') {
        throw new RuntimeException('Empty translation response');
    }
    usleep(350000);

    return $translated;
}

function translateContent(string $source): string
{
    // Preserve HTML, library shortcodes and URLs byte-for-byte; translate only text fragments.
    $parts = preg_split('~(<[^>]*>|\[\[[^\]]+\]\]|https?://[^\s<>]+)~u', $source, flags: PREG_SPLIT_DELIM_CAPTURE);
    $result = '';
    foreach ($parts as $part) {
        if (preg_match('~^(?:<|\[\[|https?://)~', $part) || trim($part) === '') {
            $result .= $part;

            continue;
        }
        preg_match('/^(\s*)(.*?)(\s*)$/su', $part, $matches);
        $translated = translateText($matches[2]);
        if (preg_match('/<[a-z][^>]*>|&[a-z]+;/iu', $source)) {
            $translated = htmlspecialchars($translated, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        }
        $result .= $matches[1].$translated.$matches[3];
    }

    return $result;
}

/** @param array<string, string> $sources
 * @return array<string, string>
 */
function translateBatch(array $sources): array
{
    $partsBySource = [];
    $fragments = [];
    foreach ($sources as $hash => $source) {
        $partsBySource[$hash] = preg_split('~(<[^>]*>|\[\[[^\]]+\]\]|https?://[^\s<>]+)~u', $source, flags: PREG_SPLIT_DELIM_CAPTURE);
        foreach ($partsBySource[$hash] as $part) {
            if (! preg_match('~^(?:<|\[\[|https?://)~', $part) && preg_match('/[А-Яа-яЁё]/u', $part)) {
                $text = trim($part);
                $fragments[hash('sha256', $text)] = $text;
            }
        }
    }
    $translatedFragments = [];
    foreach (array_chunk($fragments, 20, true) as $packet) {
        $request = '';
        foreach (array_values($packet) as $index => $text) {
            $request .= "⟦{$index}⟧\n{$text}\n";
        }
        $request .= '⟦'.count($packet).'⟧';
        $response = translateText($request);
        preg_match_all('/⟦(\d+)⟧(.*?)(?=⟦\d+⟧|$)/su', $response, $matches, PREG_SET_ORDER);
        $valid = count($matches) === count($packet) + 1;
        foreach ($matches as $index => $match) {
            $valid = $valid && (int) $match[1] === $index;
        }
        foreach (array_keys($packet) as $index => $hash) {
            $value = $valid ? trim($matches[$index][2]) : '';
            $translatedFragments[$hash] = $value !== '' ? $value : translateText($packet[$hash]);
        }
    }
    $results = [];
    foreach ($partsBySource as $hash => $parts) {
        $results[$hash] = '';
        foreach ($parts as $part) {
            $key = hash('sha256', trim($part));
            if (isset($translatedFragments[$key])) {
                preg_match('/^(\s*)(.*?)(\s*)$/su', $part, $whitespace);
                $translated = $translatedFragments[$key];
                if (preg_match('/<[a-z][^>]*>|&[a-z]+;/iu', $sources[$hash])) {
                    $translated = htmlspecialchars($translated, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
                }
                $part = $whitespace[1].$translated.$whitespace[3];
            }
            $results[$hash] .= $part;
        }
    }

    return $results;
}

try {
    if ($mode === 'export') {
        if (is_file($directory.'/source.json')) {
            throw new RuntimeException('Export already exists. Use another directory for a new snapshot.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
            throw new RuntimeException('Could not create export directory');
        }
        $snapshot = [];
        foreach ($modelClass::query()->orderBy('id')->get(['id', ...$contentFields]) as $item) {
            $fields = [];
            foreach ($contentFields as $field) {
                $source = (string) $item->getTranslation($field, 'ru', false);
                if (trim($source) !== '' && trim((string) $item->getTranslation($field, 'uk', false)) === '') {
                    $fields[$field] = $source;
                }
            }
            if ($fields !== []) {
                $snapshot[] = ['id' => $item->id, 'fields' => $fields];
            }
        }
        writeJson($directory.'/entity.json', ['entity' => $entity]);
        writeJson($directory.'/source.json', $snapshot);
        echo 'Exported '.count($snapshot)." {$entity} to {$directory}/source.json\n";
        exit(0);
    }

    $snapshotEntity = is_file($directory.'/entity.json') ? readJson($directory.'/entity.json')['entity'] : 'items';
    if ($snapshotEntity !== $entity) {
        throw new RuntimeException('Snapshot entity does not match requested entity');
    }
    $snapshot = readJson($directory.'/source.json');
    $translations = is_file($directory.'/translations.json') ? readJson($directory.'/translations.json') : [];
    if ($mode === 'translate') {
        $sources = [];
        foreach ($snapshot as $entry) {
            foreach ($entry['fields'] as $source) {
                $sources[hash('sha256', $source)] = $source;
            }
        }
        $pending = array_filter($sources, fn ($source, $hash) => ! isset($translations[$hash]) || trim($translations[$hash]) === '', ARRAY_FILTER_USE_BOTH);
        foreach (array_chunk($pending, 5, true) as $batch) {
            try {
                $translated = translateBatch($batch);
            } catch (Throwable) {
                $translated = [];
                foreach ($batch as $hash => $source) {
                    $translated[$hash] = translateText($source);
                }
            }
            $translations = array_replace($translations, $translated);
            writeJson($directory.'/translations.json', $translations);
            echo 'Translated '.count($translations).'/'.count($sources)." unique texts\n";
        }
        exit(0);
    }

    $plan = [];
    $skipped = ['missing_item' => 0, 'source_changed' => 0, 'already_translated' => 0];
    foreach ($snapshot as $entry) {
        $item = $modelClass::query()->find($entry['id'], ['id', ...$contentFields]);
        if ($item === null) {
            $skipped['missing_item']++;

            continue;
        }
        foreach ($entry['fields'] as $field => $source) {
            if ($item->getTranslation($field, 'ru', false) !== $source) {
                $skipped['source_changed']++;

                continue;
            }
            if (trim((string) $item->getTranslation($field, 'uk', false)) !== '') {
                $skipped['already_translated']++;

                continue;
            }
            $translation = $translations[hash('sha256', $source)] ?? null;
            if (! is_string($translation) || trim($translation) === '') {
                throw new RuntimeException('Missing translation for #'.$item->id.' '.$field);
            }
            $translation = str_replace([
                '[Вартова]', 'Кlan', 'Аватар на Війну', 'Пробудженіварти',
                'Відлунняколишньої', 'Клачі', 'Друк для гряди',
                'Нейрін', 'Ліс, що шепоче', 'Спустілі Долині', 'Околиця, що ожила',
                'Покинутий цвинтар', 'Катакомби Жертвопринесення', 'Гранітний Перевал',
                'Завалена Штольня', 'Каррігмор', "Попелясті Передгір'я", 'Попільні глибини',
                'Міськесховище', 'Зарослий Джерело', 'Сміттєвої Трави', 'Яр Житополі Жаху',
                'Хрести, що покосилися,', 'Тріснуте Надгробок', 'Пташиного Гніздо',
                'Перйового Осипу', 'Тунель Кладки, що обвалилася', 'Тунель Кістей, що обсипалися',
                'Тупик у Зводу, що обвалився', 'Прохід Павутини, що злиплася',
                'Розвилка Віхи, що покосилася', 'Перехрестя Чаші, Що Треснула.',
                'Глухий Тупик Бруківки, Що Треснула.', 'Ділянка Дороги у Клейма, що Стерся',
                'Гребінець Сутінків, Що Згустилися', 'Майданчик Валуна, Що Тріснув.',
                'Штрек Кріпів, що обсипаються.', 'Залишки Загорожі',
            ], [
                '[Варта]', 'Клан', 'Аватар Війни', 'Пробуджені варти',
                'Відлуння колишньої', 'Клоччя', 'Печатка для гряди',
                'Нейрин', 'Шепітний Ліс', 'Спустіла Долина', 'Ожила околиця',
                'Покинуте кладовище', 'Катакомби Жертвопринесень', 'Гранітний Перевал',
                'Завалена Штольня', 'Каррігмор', "Попелясті Передгір'я", 'Попелясті Глибини',
                'Міське сховище', 'Заросле джерело', "Бур'яну", 'Яр велетенського жаху',
                'Похилені хрести', 'Тріснутий надгробок', 'Пташиного гнізда',
                "Пір'яного осипу", 'Тунель обваленої кладки', 'Тунель кісток, що обсипалися',
                'Тупик під обваленим склепінням', 'Прохід злиплої павутини',
                'Розвилка похиленої віхи', 'Перехрестя тріснутої чаші',
                'Глухий тупик тріснутої бруківки', 'Ділянка дороги біля стертої печатки',
                'Гребінь згущених сутінків', 'Майданчик тріснутого валуна',
                'Штрек осипаних кріплень', 'Залишки огорожі',
            ], $translation);
            $translation = str_replace([
                'Нічні тварини', 'Ліч Некромант', 'Здолайте Лича Некроманта',
                'Убийте Миша ', 'Убийте Кажан ', 'Вразіть Володаря Нежиті',
            ], [
                'Нічні створіння', 'Ліч-некромант', 'Переможіть Ліча-некроманта',
                'Убийте Мишу ', 'Убийте Кажана ', 'Переможіть Володаря Нежиті',
            ], $translation);
            $translation = str_replace([
                'називають їхГнильні', 'СмолуТернівника', 'зросталосамотнє', 'Задень такий корінь',
                'завсієї ширини', 'Метельних Духів', 'Метельний Дух', 'пеленою,що', 'не береться.відповісти.',
                'Охолілим Вугіллям Пожарища', 'Охолілі Вугілля Пожарища',
            ], [
                'називають їх Гнильні', 'смолу Тернівника', 'росло самотнє', 'Зачепи такий корінь',
                'по всій ширині', 'Заметільних Духів', 'Заметільний Дух', 'пеленою, що', 'не береться відповісти.',
                'Охолодженим Вугіллям Пожарища', 'Охолоджене Вугілля Пожарища',
            ], $translation);
            if ($entity === 'event_activities') {
                $translation = str_replace(
                    ['>Миша<', '>Кажан<', '>Лича Некроманта<', 'Вразіть <strong>'],
                    ['>Мишу<', '>Кажана<', '>Ліча-некроманта<', 'Переможіть <strong>'],
                    $translation,
                );
                $translation = preg_replace_callback(
                    '~(<strong><span style="color:green">(\\d+)</span></strong>) раз$~u',
                    static function (array $matches): string {
                        $count = (int) $matches[2];
                        $lastTwo = $count % 100;
                        $last = $count % 10;
                        $word = $lastTwo >= 11 && $lastTwo <= 14 ? 'разів' : match ($last) {
                            1 => 'раз',
                            2, 3, 4 => 'рази',
                            default => 'разів',
                        };

                        return $matches[1].' '.$word;
                    },
                    $translation,
                ) ?? $translation;
            }
            if ($entity === 'library_articles') {
                $translation = str_replace([
                    'Професіявидобутку', 'деревини:рівні', 'перевагунад',
                    'шанскритичного', 'ризикованевплавлення', 'Зломщик',
                ], [
                    'Професія видобутку', 'деревини: рівні', 'перевагу над',
                    'шанс критичного', 'ризиковане вплавлення', 'Зломник',
                ], $translation);
            }
            if ($entity === 'clan_skill_definitions') {
                $translation = str_replace([
                    'підвищують чинність',
                    'всіх членів',
                    'Своя закрита лінія мани, проведена крізь землі клану, збільшує запас мани членів.',
                    'Бібліотека клану відкрита всім — спільне вивчення праць підвищує інтелект членів.',
                    'Бойовий клич клану піднімає бойовий запал - збільшує атаку в бою.',
                    'Ковалі клану кують за загальним зразком – підвищує захист усіх членів.',
                ], [
                    'збільшують силу',
                    'усіх членів',
                    'Прихований потік мани, що проходить землями клану, збільшує запас мани його членів.',
                    'Бібліотека клану відкрита усім — спільне вивчення праць розвиває інтелект його членів.',
                    'Бойовий клич клану додає бойового запалу й посилює атаку.',
                    'Ковалі клану виготовляють спорядження за спільним зразком, підвищуючи захист усіх членів.',
                ], $translation);
            }
            preg_match_all('~<[^>]*>|\[\[[^\]]+\]\]|https?://[^\s<>]+~u', $source, $sourceTokens);
            preg_match_all('~<[^>]*>|\[\[[^\]]+\]\]|https?://[^\s<>]+~u', $translation, $translatedTokens);
            if ($sourceTokens[0] !== $translatedTokens[0]) {
                throw new RuntimeException('Markup or shortcode changed for #'.$item->id.' '.$field);
            }
            if (($field === 'name' || $field === 'title' || $field === 'reply_text' || $field === 'button_text' || str_ends_with($field, '_name')) && mb_strlen($translation) > 255) {
                throw new RuntimeException('Translated name too long for #'.$item->id);
            }
            $plan[$item->id][$field] = ['ru' => $source, 'uk' => $translation];
        }
    }
    writeJson($directory.'/preview.json', ['entity' => $entity, 'items' => $plan, 'skipped' => $skipped]);
    echo count($plan).' '.$entity.'; '.array_sum(array_map('count', $plan)).' fields; skipped: '.json_encode($skipped)."\n";
    if ($mode === 'preview') {
        exit(0);
    }

    // Serialize cooperating runs, recheck every source under row locks and preserve all other locales.
    $lock = fopen($directory.'/apply.lock', 'c');
    if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
        throw new RuntimeException('Another import is running');
    }
    $before = [];
    $applied = 0;
    DB::transaction(function () use ($plan, $directory, $modelClass, $contentFields, &$before, &$applied): void {
        foreach ($plan as $id => $fields) {
            $item = $modelClass::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            foreach ($fields as $field => $values) {
                if ($item->getTranslation($field, 'ru', false) !== $values['ru'] || trim((string) $item->getTranslation($field, 'uk', false)) !== '') {
                    throw new RuntimeException('Concurrent content change for #'.$id.'. Import rolled back.');
                }
            }
            foreach ($contentFields as $field) {
                $before[$id][$field] = $item->getRawOriginal($field);
            }
        }
        writeJson($directory.'/before-apply-'.date('Ymd-His').'.json', $before);
        foreach ($plan as $id => $fields) {
            $item = $modelClass::query()->findOrFail($id);
            foreach ($fields as $field => $values) {
                $item->setTranslation($field, 'uk', $values['uk']);
            }
            $item->saveQuietly();
            $applied++;
        }
    });
    echo "Applied translations to {$applied} {$entity}. Russian text was not changed.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
