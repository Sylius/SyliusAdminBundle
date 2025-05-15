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

namespace Sylius\Bundle\AdminBundle\Tests\Action\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\AdminBundle\Action\Account\RequestPasswordResetAction;
use Sylius\Bundle\AdminBundle\Form\Model\PasswordResetRequest;
use Sylius\Bundle\AdminBundle\Form\RequestPasswordResetType;
use Sylius\Bundle\CoreBundle\Command\Admin\Account\RequestResetPasswordEmail;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class RequestPasswordResetActionTest extends TestCase
{
    private FormFactoryInterface&MockObject $formFactoryMock;

    private MessageBusInterface&MockObject $messageBusMock;

    private MockObject&RequestStack $requestStackMock;

    private MockObject&RouterInterface $routerMock;

    private Environment&MockObject $twigMock;

    private MockObject&Session $sessionMock;

    private FlashBagInterface&MockObject $flashBagMock;

    private RequestPasswordResetAction $requestPasswordResetAction;

    protected function setUp(): void
    {
        $this->formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->twigMock = $this->createMock(Environment::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->flashBagMock = $this->createMock(FlashBagInterface::class);

        $this->requestPasswordResetAction = new RequestPasswordResetAction(
            $this->formFactoryMock,
            $this->messageBusMock,
            $this->requestStackMock,
            $this->routerMock,
            $this->twigMock,
        );
    }

    public function testSendsResetPasswordRequestToMessageBus(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $requestMock = $this->createMock(Request::class);
        $attributesBagMock = $this->createMock(ParameterBag::class);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(RequestPasswordResetType::class)
            ->willReturn($formMock)
        ;

        $formMock->expects($this->once())->method('handleRequest')->with($requestMock)->willReturnSelf();
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(true);

        $passwordResetRequest = new PasswordResetRequest();
        $passwordResetRequest->setEmail('sylius@example.com');

        $formMock->expects($this->once())->method('getData')->willReturn($passwordResetRequest);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RequestResetPasswordEmail::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->flashBagMock
            ->expects($this->once())
            ->method('add')
            ->with('success', 'sylius.admin.request_reset_password.success')
        ;

        $this->sessionMock
            ->expects($this->once())
            ->method('getBag')
            ->with('flashes')
            ->willReturn($this->flashBagMock)
        ;

        $this->requestStackMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);

        $attributesBagMock
            ->expects($this->once())
            ->method('get')
            ->with('_sylius', [])
            ->willReturn(['redirect' => 'my_custom_route'])
        ;

        $requestMock->attributes = $attributesBagMock;

        $this->routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('my_custom_route')
            ->willReturn('/login')
        ;

        $response = $this->requestPasswordResetAction->__invoke($requestMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testItSendsResetPasswordRequestWhenSyliusRedirectParameterIsArray(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $requestMock = $this->createMock(Request::class);
        $attributesBagMock = $this->createMock(ParameterBag::class);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(RequestPasswordResetType::class)
            ->willReturn($formMock)
        ;

        $formMock->expects($this->once())->method('handleRequest')->with($requestMock)->willReturnSelf();
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(true);

        $passwordResetRequest = new PasswordResetRequest();
        $passwordResetRequest->setEmail('sylius@example.com');

        $formMock->expects($this->once())->method('getData')->willReturn($passwordResetRequest);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RequestResetPasswordEmail::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->flashBagMock
            ->expects($this->once())
            ->method('add')
            ->with('success', 'sylius.admin.request_reset_password.success')
        ;

        $this->sessionMock
            ->expects($this->once())
            ->method('getBag')
            ->with('flashes')
            ->willReturn($this->flashBagMock)
        ;

        $this->requestStackMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);

        $route = 'my_custom_route';
        $params = ['my_parameter' => 'my_value'];

        $attributesBagMock
            ->expects($this->once())
            ->method('get')
            ->with('_sylius', [])
            ->willReturn([
                'redirect' => [
                    'route' => $route,
                    'params' => $params,
                ],
            ])
        ;

        $requestMock->attributes = $attributesBagMock;

        $this->routerMock
            ->expects($this->once())
            ->method('generate')
            ->with($route, $params)
            ->willReturn('/login')
        ;

        $response = $this->requestPasswordResetAction->__invoke($requestMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testItRedirectsToDefaultRouteIfCustomOneIsNotDefined(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $requestMock = $this->createMock(Request::class);
        $attributesBagMock = $this->createMock(ParameterBag::class);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(RequestPasswordResetType::class)
            ->willReturn($formMock)
        ;

        $formMock->expects($this->once())->method('handleRequest')->with($requestMock)->willReturnSelf();
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(true);

        $passwordResetRequest = new PasswordResetRequest();
        $passwordResetRequest->setEmail('sylius@example.com');

        $formMock->expects($this->once())->method('getData')->willReturn($passwordResetRequest);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RequestResetPasswordEmail::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->flashBagMock
            ->expects($this->once())
            ->method('add')
            ->with('success', 'sylius.admin.request_reset_password.success')
        ;

        $this->sessionMock
            ->expects($this->once())
            ->method('getBag')
            ->with('flashes')
            ->willReturn($this->flashBagMock)
        ;

        $this->requestStackMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);

        $attributesBagMock
            ->expects($this->once())
            ->method('get')
            ->with('_sylius', [])
            ->willReturn([])
        ;

        $requestMock->attributes = $attributesBagMock;

        $this->routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_login')
            ->willReturn('/login')
        ;

        $response = $this->requestPasswordResetAction->__invoke($requestMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testItRendersFormWithErrorsWhenRequestIsNotValid(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $formViewMock = $this->createMock(FormView::class);
        $requestMock = $this->createMock(Request::class);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(RequestPasswordResetType::class)
            ->willReturn($formMock)
        ;

        $formMock->expects($this->once())->method('handleRequest')->with($requestMock)->willReturnSelf();
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(false);

        $this->messageBusMock->expects($this->never())->method('dispatch');

        $formMock->expects($this->once())->method('createView')->willReturn($formViewMock);

        $this->twigMock
            ->expects($this->once())
            ->method('render')
            ->with($this->isType('string'), ['form' => $formViewMock])
            ->willReturn('responseContent')
        ;

        $response = $this->requestPasswordResetAction->__invoke($requestMock);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('responseContent', $response->getContent());
    }
}
