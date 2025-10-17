<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Assert;

describe('Happy Path', function (): void {
    test('lazy assertion validates multiple array fields successfully', function (): void {
        $form = [
            'email' => 'Richard@Home.com',
            'password' => 'Some highly secret password',
        ];

        expect(Assert::lazy()
            ->that($form['email'] ?? null, 'email')
            ->notEmpty()
            ->maxLength(255)
            ->email()
            ->that($form['password'] ?? null, 'password')
            ->notEmpty()
            ->minLength(4)
            ->maxLength(255)
            ->verifyNow())->toBeTrue();
    });
});
