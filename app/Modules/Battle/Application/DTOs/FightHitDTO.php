<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\DTOs;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Support\Collection;

final class FightHitDTO
{
    protected int $damage;

    /** Урон, погашенный блоком щита и отражённый обратно атакующему (0 — блок не сработал) */
    protected int $reflectedDamage = 0;

    /**
     * Сработал ли блок щита. Отдельный флаг, а не «reflectedDamage > 0»:
     * applyShieldBlock() гасит максимум damage-1, поэтому на удар в 1 урон
     * блок срабатывает, но гасит 0 — по отражению такой блок неотличим от
     * его отсутствия, а навык «Владение щитом» должен его засчитать.
     */
    protected bool $blocked = false;

    /** Сколько урона погасила броня цели (для строки лога); 0 — броня ничего не сняла */
    protected int $armorAbsorbed = 0;

    protected bool $dodge = false;

    protected bool $critical = false;

    protected ?ShareItem $weapon = null;

    protected string $weaponName;

    /**
     * 'left'/'right' — какой физический слот руки нанёс этот хит (для рун:
     * пассивка привязана к конкретному предмету, а не ко всем ударам сразу).
     * null — хит не от оружия в руке (кулак, магический скилл).
     */
    protected ?string $handSide = null;

    protected ?Skill $skill = null;

    protected bool $cantCast = false;

    protected ?string $message = null;

    protected ?MagicSkill $magicSkill = null;

    /** Боеприпас выстрела (стрела из колчана) — для фраз лога. */
    protected ?ShareItem $ammo = null;

    protected Collection $appliedEffects;

    protected Collection $selfAppliedEffects;

    /**
     * Новий незалежний кидок цього ж удару (той самий діапазон урону й навик, свій крит/ухилення).
     * Задає стратегія зброї; використовує «Подвійний удар» руни. null — удар не можна повторити (магія).
     *
     * @var (\Closure(): self)|null
     */
    protected ?\Closure $reroll = null;

    public function __construct()
    {
        $this->appliedEffects = collect();
        $this->selfAppliedEffects = collect();
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): self
    {
        $this->damage = $damage;

        return $this;
    }

    public function getReflectedDamage(): int
    {
        return $this->reflectedDamage;
    }

    public function setReflectedDamage(int $reflectedDamage): self
    {
        $this->reflectedDamage = $reflectedDamage;

        return $this;
    }

    public function getArmorAbsorbed(): int
    {
        return $this->armorAbsorbed;
    }

    public function setArmorAbsorbed(int $armorAbsorbed): self
    {
        $this->armorAbsorbed = $armorAbsorbed;

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function isDodge(): bool
    {
        return $this->dodge;
    }

    public function setDodge(bool $dodge): self
    {
        $this->dodge = $dodge;

        return $this;
    }

    public function getAmmo(): ?ShareItem
    {
        return $this->ammo;
    }

    public function setAmmo(?ShareItem $ammo): self
    {
        $this->ammo = $ammo;

        return $this;
    }

    public function getWeapon(): ?ShareItem
    {
        return $this->weapon;
    }

    public function setWeapon(?ShareItem $weapon): self
    {
        $this->weapon = $weapon;

        return $this;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function setCritical(bool $critical): self
    {
        $this->critical = $critical;

        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill = null): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function getWeaponName(): string
    {
        return $this->weaponName;
    }

    public function setWeaponName(string $weaponName): self
    {
        $this->weaponName = $weaponName;

        return $this;
    }

    public function getHandSide(): ?string
    {
        return $this->handSide;
    }

    public function setHandSide(?string $handSide): self
    {
        $this->handSide = $handSide;

        return $this;
    }

    public function isCantCast(): bool
    {
        return $this->cantCast;
    }

    public function setCantCast(bool $cantCast): self
    {
        $this->cantCast = $cantCast;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMagicSkill(): ?MagicSkill
    {
        return $this->magicSkill;
    }

    public function setMagicSkill(?MagicSkill $magicSkill): self
    {
        $this->magicSkill = $magicSkill;

        return $this;
    }

    public function addAppliedEffect(Effect $effect, ?int $tickValue = null): self
    {
        $this->appliedEffects->push(['effect' => $effect, 'tickValue' => $tickValue]);

        return $this;
    }

    /** @return Collection<int, array{effect: Effect, tickValue: ?int}> */
    public function getAppliedEffects(): Collection
    {
        return $this->appliedEffects;
    }

    public function addSelfAppliedEffect(Effect $effect): self
    {
        $this->selfAppliedEffects->push($effect);

        return $this;
    }

    public function getSelfAppliedEffects(): Collection
    {
        return $this->selfAppliedEffects;
    }

    /** @param  (\Closure(): self)|null  $reroll */
    public function setReroll(?\Closure $reroll): self
    {
        $this->reroll = $reroll;

        return $this;
    }

    /** Новий кидок удару з тією ж зброєю/рукою/набоєм; null — якщо удар не повторюваний. */
    public function reroll(): ?self
    {
        if ($this->reroll === null) {
            return null;
        }

        return ($this->reroll)()
            ->setWeaponName($this->weaponName)
            ->setSkill($this->skill)
            ->setWeapon($this->weapon)
            ->setAmmo($this->ammo)
            ->setHandSide($this->handSide);
    }
}
