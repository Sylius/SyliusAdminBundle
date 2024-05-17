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

namespace Sylius\Bundle\AdminBundle\Form\Extension;

use Sylius\Bundle\PromotionBundle\Form\Type\CatalogPromotionScopeType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

trigger_deprecation(
    'sylius/admin-bundle',
    '1.14',
    'The "%s" class is deprecated and will be removed in Sylius 2.0.',
    CatalogPromotionScopeTypeExtension::class,
);

/** @deprecated since Sylius 1.14 and will be removed in Sylius 2.0. */
final class CatalogPromotionScopeTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class);
    }

    public static function getExtendedTypes(): iterable
    {
        yield CatalogPromotionScopeType::class;
    }
}
