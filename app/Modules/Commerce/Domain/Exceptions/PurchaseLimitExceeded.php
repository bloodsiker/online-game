<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Domain\Exceptions;

use DomainException;

final class PurchaseLimitExceeded extends DomainException {}
