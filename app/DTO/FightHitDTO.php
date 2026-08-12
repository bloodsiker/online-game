<?php

namespace App\DTO;

use App\Models\Skill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
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

    protected Collection $appliedEffects;

    protected Collection $selfAppliedEffects;

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

    public function addAppliedEffect(Effect $effect): self
    {
        $this->appliedEffects->push($effect);

        return $this;
    }

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
}
