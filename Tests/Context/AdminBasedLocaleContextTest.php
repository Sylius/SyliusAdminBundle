<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Bundle\AdminBundle\Context;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\AdminBundle\Context\AdminBasedLocaleContext;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class AdminBasedLocaleContextTest extends TestCase
{
    private MockObject&TokenStorageInterface $tokenStorageMock;

    private AdminBasedLocaleContext $adminBasedLocaleContext;

    protected function setUp(): void
    {
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->adminBasedLocaleContext = new AdminBasedLocaleContext($this->tokenStorageMock);
    }

    public function testImplementsLocaleContextInterface(): void
    {
        $this->assertInstanceOf(LocaleContextInterface::class, $this->adminBasedLocaleContext);
    }

    public function testThrowsLocaleNotFoundExceptionWhenThereIsNoToken(): void
    {
        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn(null);
        $this->expectException(LocaleNotFoundException::class);
        $this->adminBasedLocaleContext->getLocaleCode();
    }

    public function testThrowsLocaleNotFoundExceptionWhenThereIsNoUserInTheToken(): void
    {
        $tokenMock = $this->createMock(TokenInterface::class);

        $tokenMock->expects($this->once())->method('getUser')->willReturn(null);

        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn($tokenMock);
        $this->expectException(LocaleNotFoundException::class);
        $this->adminBasedLocaleContext->getLocaleCode();
    }

    public function testThrowsLocaleNotFoundExceptionWhenTheUserTakenFromTokenIsNotAnAdmin(): void
    {
        $tokenMock = $this->createMock(TokenInterface::class);
        $userMock = $this->createMock(UserInterface::class);

        $tokenMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn($tokenMock);
        $this->expectException(LocaleNotFoundException::class);
        $this->adminBasedLocaleContext->getLocaleCode();
    }

    public function testReturnsLocaleOfCurrentlyLoggedAdminUser(): void
    {
        $tokenMock = $this->createMock(TokenInterface::class);
        $adminMock = $this->createMock(AdminUserInterface::class);

        $adminMock->expects($this->once())->method('getLocaleCode')->willReturn('en_US');
        $tokenMock->expects($this->once())->method('getUser')->willreturn($adminMock);

        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn($tokenMock);
        $this->assertSame('en_US', $this->adminBasedLocaleContext->getLocaleCode());
    }
}
