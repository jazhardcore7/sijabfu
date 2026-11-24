<?php

namespace App\Exceptions;

use DomainException;

/**
 * Perpindahan status usulan yang tidak diizinkan oleh state machine (PRD Revisi bagian 4).
 * Controller memetakannya ke HTTP 422 (fase Monitoring).
 */
class InvalidStatusTransition extends DomainException
{
    public static function from(?string $from, string $to, string $reason = ''): self
    {
        $message = sprintf(
            'Perubahan status dari "%s" ke "%s" tidak diizinkan.',
            $from ?? 'baru',
            $to
        );

        return new self(trim($message.' '.$reason));
    }
}
