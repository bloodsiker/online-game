---
type: "query"
date: "2026-08-11T13:22:23.056957+00:00"
question: "а для такой игры лучше mysql posgres?"
contributor: "graphify"
outcome: "useful"
source_nodes: ["Battle", "FightOrchestrator", "BattleRound", "Player"]
---

# Q: а для такой игры лучше mysql posgres?

## Answer

Expanded from original query via graph vocab: [database, transaction, query, battle, round, hit, history, index, model, repository, player, monster]. Для существующего проекта рекомендован MySQL 8. Текущий bottleneck связан с количеством запросов и PHP-FPM, а не с невозможностью MySQL: нагрузочный прогон дал 0 HTTP-ошибок, 0 physical reads, только 4 новых row-lock waits и 84 ms lock time. PostgreSQL не устранит сериализацию атак: текущий FightOrchestrator использует transaction и lockForUpdate, а конфликтующие writers на одной строке ждут и в PostgreSQL. Миграция потребует переписать MySQL-специфичные ENUM ALTER TABLE, unsigned-типы и after clauses. PostgreSQL стоит выбрать для нового проекта или при доказанной необходимости JSONB, partial indexes, PostGIS и сложной аналитики. Для роста игры важнее составные индексы battle_details(battle_id,status), battle_rounds(battle_id,round_number), архивирование боевых логов и BIGINT для денег и опыта.

## Outcome

- Signal: useful

## Source Nodes

- Battle
- FightOrchestrator
- BattleRound
- Player