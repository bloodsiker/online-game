"""Summarize the read-only mage FightOrchestrator audit into Markdown and CSV."""
import csv
import json
import sys
from pathlib import Path

folder = Path(__file__).resolve().parents[1] / "storage/app/reports"
prefix = sys.argv[1] if len(sys.argv) > 1 else "mage-audit-20261029"
max_level = int(sys.argv[2]) if len(sys.argv) > 2 else 55
data = {p: json.loads((folder / f"{prefix}-{p}.json").read_text()) for p in ("staff", "folio")}
labels = {"staff": "Посох", "folio": "Фолиант"}
by_level = {p: {level: [r for r in d["results"] if r["level"] == level] for level in range(1, max_level+1)} for p, d in data.items()}

def summarize(profile, level):
    rows = by_level[profile][level]
    if not rows:
        return None
    fights, wins, rounds = (sum(r[k] for r in rows) for k in ("fights", "wins", "rounds"))
    b = data[profile]["builds"][str(level)]
    return dict(level=level, monsters=len(rows), fights=fights, wins=wins, win_rate=100*wins/fights,
                rounds=rounds/fights, hp_left=100*sum(r["hp_left"] for r in rows)/wins/b["hp"] if wins else 0,
                mp_left=100*sum(r["mp_left"] for r in rows)/wins/b["mp"] if wins and b["mp"] else 0,
                physical=100*sum(r["physical_fallbacks"] for r in rows)/rounds if rounds else 0)

with (folder / f"{prefix}-levels.csv").open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.writer(f)
    writer.writerow(["Профиль","Уровень","Виды мобов","Боёв","Побед","Победы %","Ходы","HP остаток %","MP остаток %","Обычные удары %"])
    for level in range(1, max_level+1):
        for p in data:
            s = summarize(p, level)
            if s:
                writer.writerow([labels[p], *[round(v,3) if isinstance(v,float) else v for v in s.values()]])

with (folder / f"{prefix}-monsters.csv").open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.writer(f)
    writer.writerow(["Профиль","Уровень","ID монстра","Монстр","HP моба","Боёв","Побед","Поражений","Ничьих","Победы %","Ходы","HP остаток %","MP остаток %","Обычные удары"])
    for p,d in data.items():
        for r in d["results"]:
            b = d["builds"][str(r["level"])]
            writer.writerow([labels[p],r["level"],r["monster_id"],r["monster"],r["monster_hp"],r["fights"],r["wins"],r["losses"],r["draws"],
                round(100*r["wins"]/r["fights"],2),round(r["rounds"]/r["fights"],2),
                round(100*r["hp_left"]/r["wins"]/b["hp"],2) if r["wins"] else 0,
                round(100*r["mp_left"]/r["wins"]/b["mp"],2) if r["wins"] and b["mp"] else 0,r["physical_fallbacks"]])

with (folder / f"{prefix}-builds.csv").open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.writer(f)
    writer.writerow(["Профиль","Уровень","Навык","HP","MP","Интеллект","Мудрость","Выносливость","Броня","Маг. атака","Маг. сопротивление","Маг. крит %","Маг. крит урон %","Экипировка","Заклинания"])
    for level in range(1,max_level+1):
        for p,d in data.items():
            b = d["builds"][str(level)]
            s = b["stats"]
            writer.writerow([labels[p],level,b["skill_level"],b["hp"],b["mp"],s["intelligence"],s["wisdom"],s["endurance"],s["armor"],s["magicAttack"],s["magicResistance"],s["magicCritical"],s["magicCritDamage"],
                "; ".join(f'{i["name"]} [#{i["id"]}]' for i in b["gear"].values()),"; ".join(i["name"] for i in b["spells"])])

lines = [f"# Маг 1–{max_level}: реальные боевые сервисы", "", "Дата: 29.10.2026.", "", "## Условия", "",
    "- Справочники взяты из текущей БД. Игровая база использовалась только для SELECT/SHOW; бои, игроки и предметы существуют в отдельной SQLite :memory:.",
    "- Раса: эльф. Учтён настоящий расовый прирост всех характеристик.",
    "- Свободные очки: 50% интеллект, 20% мудрость, оставшиеся 30% выносливость.",
    "- Навыки владения оружием и колдовства равны уровню персонажа. Это условие проверки, а не эмуляция накопления опыта навыка.",
    "- Два варианта: посох и фолиант. Сфера добавляется при свободной второй руке.",
    "- Экипировка выбирается автоматическим скорингом из доступных базовых предметов Пепельной Башни, Инея/Всполоха и Архимага/Астрала. Поздний предмет не заменяет ранний, если его оценка ниже. Все требования проверяются ItemRequirementService. Для предметов без требования уровня дополнительно применены рекомендуемые пороги тира и слота.",
    "- Без заточки, артефактов, камней, рун, премиальных, клановых и репутационных усилений.",
    "- Доступность заклинаний проверяется MagicSkillRequirementService; более высокий ранг заменяет предыдущий.",
    "- Тактика: доступная атака с наибольшим ожидаемым прямым уроном; лечение при HP ниже 40%; при нехватке маны — обычный удар. Активные баффы и контроль отдельно не оптимизировались.",
    "- Каждый бой начинается с полными HP/MP; шаг времени — 2 секунды; предел — 150 ходов.",
    "- Использован FightOrchestrator с настоящими расчётами характеристик, маны, критов, сопротивлений, эффектов и регенерации. Случайные броски выполняются игровым кодом.",
    "- По 100 боёв с каждым обычным монстром равного уровня, размещённым на локациях. Боссы и PvP не включены.",
    "", "## Итог", ""]
for p,d in data.items():
    total,wins,losses,draws = (sum(r[k] for r in d["results"]) for k in ("fights","wins","losses","draws"))
    lines.append(f'- **{labels[p]}:** {total} боёв, {wins} побед, {losses} поражений, {draws} ничьих; победы {100*wins/total:.2f}%.')
missing = [str(l) for l in range(1,max_level+1) if not by_level["staff"][l]]
lines += ["", "Уровни без размещённых обычных мобов равного уровня: "+", ".join(missing)+". Для них рассчитан билд, но результат боя не выдумывался.",
    "", "## Все уровни", "", "Ходы — среднее число действий до конца боя. HP — средний остаток здоровья после победы. П/Ф — посох / фолиант. Проценты относятся к выборке, а не гарантируют исход будущих боёв.", "",
    "| Ур. | Виды мобов | Боёв П/Ф | Победы П | Победы Ф | Ходы П/Ф | HP остаток П/Ф |",
    "|---:|---:|---:|---:|---:|---:|---:|"]
for level in range(1,max_level+1):
    a,b = summarize("staff",level),summarize("folio",level)
    if not a:
        lines.append(f"| {level} | 0 | — | — | — | — | — |")
    else:
        lines.append(f'| {level} | {a["monsters"]} | {a["fights"]}/{b["fights"]} | {a["win_rate"]:.1f}% | {b["win_rate"]:.1f}% | {a["rounds"]:.2f}/{b["rounds"]:.2f} | {a["hp_left"]:.1f}%/{b["hp_left"]:.1f}% |')
lines += ["", "## Самые тяжёлые противники", "", "| Профиль | Ур. | Монстр | Победы | Ходы | HP остаток |", "|---|---:|---|---:|---:|---:|"]
hardest = []
for p,d in data.items():
    for r in d["results"]:
        hp = 100*r["hp_left"]/max(1,r["wins"])/d["builds"][str(r["level"])]["hp"]
        hardest.append((r["wins"]/r["fights"],hp,p,r))
for _,hp,p,r in sorted(hardest,key=lambda x:(x[0],x[1]))[:15]:
    lines.append(f'| {labels[p]} | {r["level"]} | {r["monster"]} | {100*r["wins"]/r["fights"]:.1f}% | {r["rounds"]/r["fights"]:.2f} | {hp:.1f}% |')
lines += ["", "## Подробности", "",
    f"- {prefix}-levels.csv — статистика по уровням.",
    f"- {prefix}-monsters.csv — каждая пара профиль / монстр.",
    f"- {prefix}-builds.csv — характеристики, экипировка и заклинания всех {max_level} уровней.",
    f"- {prefix}-staff.json и {prefix}-folio.json — исходные результаты.", "",
    f"- {prefix}-catalog.json — снимок характеристик монстров, эффектов, заклинаний и предметов.", "",
    "## Наблюдения по данным и механике", "",
    "- Все поражения основного прогона произошли против Плакальщиц и Чёрных Гадюк. В каталоге им назначен длительный паралич: 60 секунд у Плакальщицы и 50 секунд у Гадюки.",
    "- Проверочный лог Плакальщицы 42-го уровня подтверждает поражение после наложения паралича. Ожог снижает её HP до 1, но не добивает: последний удар должен нанести игрок, который всё ещё парализован.",
    "- У Фолианта Пепельной Башни в БД +15 магического крита и +25 критурона, у Фолианта Иней +10 магического крита, у Фолианта Астрал магический крит отсутствует. Поэтому используемый скоринг оставил ранний фолиант даже на 55-м уровне. Это необходимо учитывать при оценке прогрессии экипировки.",
    "- Посохи Архимаг и Астрал в текущей БД не отмечены двуручными; посохи Пепельной Башни и Иней отмечены. Вторая рука определяется этим реальным флагом.",
    "- Сто боёв на пару дают первичную оценку. Разница в несколько побед между посохом и фолиантом на отдельных монстрах может быть случайной.",
    "",
    "Это оценка базового PvE, а не максимальной силы со всеми усилениями или экономики непрерывной прокачки. Повторяемые предметы со снятыми требованиями не используются для преждевременного доступа к поздним тирам.", ""]
report = folder / f"{prefix}.md"
report.write_text("\n".join(lines))
print(report)
for p,d in data.items():
    print(p, "pairs", len(d["results"]), "fights", sum(r["fights"] for r in d["results"]), "losses", sum(r["losses"] for r in d["results"]))
