<?php

namespace App\DTO;


use App\Models\MagicSkill\Effect;
use App\Models\MagicSkill\MagicSkill;
use App\Models\ShareItem;
use App\Models\Skill;
use Illuminate\Support\Collection;

class FightHitDTO
{
    protected int $damage;
    protected bool $dodge = false;
    protected bool $critical = false;
    protected ?ShareItem $weapon = null;
    protected string $weaponName;
    protected ?Skill $skill = null;
    protected bool $cantCast = false;
    protected ?string $message = null;
    protected ?MagicSkill $magicSkill = null;

    protected Collection $appliedEffects;

    public function __construct()
    {
        $this->appliedEffects = collect();
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
}
