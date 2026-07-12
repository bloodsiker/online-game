---
type: "query"
date: "2026-07-11T07:10:29.532777+00:00"
question: "изучи систему боя App\\Modules\\Battle и баланс между треугольником уворот-крит-танк. Не нарушен ли баланс и какие твои будут советы?"
contributor: "graphify"
outcome: "useful"
source_nodes: ["HitCalculator", "Player", "PlayerStatService", "FightOrchestrator", "MonsterAttackService", "SimulateBattleTriangle"]
---

# Q: изучи систему боя App\Modules\Battle и баланс между треугольником уворот-крит-танк. Не нарушен ли баланс и какие твои будут советы?

## Answer

Expanded from original query via graph vocab: [battle, attack, hit, dodge, critical, crit, armor, defense, damage, stats, player, calculator]. Баланс нарушен. Production PvE передает raw Player в MonsterAttackService, где armor/dodge/critical равны нулю, поэтому симулятор не отражает игру. При HP от strength танк доминирует 96-100%; при равном HP на endgame доминирует крит из-за фиксированного K и неограниченного crit damage. statChance обнуляется при delta=-3 и имеет фактический cap 75%, хотя комментарии обещают 60%. Рекомендации: сначала единый resolved StatSheet для атаки и защиты, затем level-based HP, level-scaled rating curves, softcap crit damage, class purity сверх 1/3, deterministic PvE simulations и regression tests.

## Outcome

- Signal: useful

## Source Nodes

- HitCalculator
- Player
- PlayerStatService
- FightOrchestrator
- MonsterAttackService
- SimulateBattleTriangle