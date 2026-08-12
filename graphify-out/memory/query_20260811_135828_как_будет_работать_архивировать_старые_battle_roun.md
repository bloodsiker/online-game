---
type: "query"
date: "2026-08-11T13:58:28.893429+00:00"
question: "как будет работать Архивировать старые battle_rounds и battle_round_hits.?"
contributor: "graphify"
outcome: "useful"
source_nodes: ["Battle", "BattleRound", "BattleRoundHit", "FightController"]
---

# Q: как будет работать Архивировать старые battle_rounds и battle_round_hits.?

## Answer

Expanded from original query via graph vocab: [battle, round, hit, history, log, finish, status, delete, repository, database, transaction, controller]. Для текущего сценария просмотра одного боя архив лучше хранить как самостоятельный gzip JSON по battle_id в private local или S3-compatible storage. battles и battle_details остаются в MySQL для списка и краткой сводки; только завершённые бои старше retention переносятся. Команда создаёт snapshot с именами и HP участников, rounds и hits, записывает файл по детерминированному пути, проверяет JSON, count и SHA-256, затем в DB transaction создаёт battle_log_archives, ставит battles.logs_archived_at и удаляет battle_rounds. Связанные battle_round_hits удалятся по ON DELETE CASCADE, поэтому удаление разрешено только после успешной проверки архива. FightController должен читать через BattleLogRepository: свежий бой из Eloquent, архивный из Storage, возвращая одинаковый DTO в Blade. Добавить finished_at, logs_archived_at, metadata table, dry-run, chunking, unique battle_id и scheduled withoutOverlapping. На текущих примерно 2954 rounds и 3465 hits архив ещё не срочен.

## Outcome

- Signal: useful

## Source Nodes

- Battle
- BattleRound
- BattleRoundHit
- FightController