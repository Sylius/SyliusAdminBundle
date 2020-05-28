<?php

namespace Sylius\Bundle\AdminBundle\Provider;

use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Component\Core\Dashboard\DashboardStatisticsProviderInterface;
use Sylius\Component\Core\Dashboard\Interval;
use Sylius\Component\Core\Dashboard\SalesDataProviderInterface;
use Sylius\Component\Core\Model\ChannelInterface;

class StatisticsDataProvider implements StatisticsDataProviderInterface
{
    /** @var DashboardStatisticsProviderInterface */
    private $statisticsProvider;

    /** @var SalesDataProviderInterface */
    private $salesDataProvider;

    /** @var MoneyFormatterInterface */
    private $moneyFormatter;


    public function __construct(
        DashboardStatisticsProviderInterface $statisticsProvider,
        SalesDataProviderInterface $salesDataProvider,
        MoneyFormatterInterface $moneyFormatter
    ) {
        $this->statisticsProvider = $statisticsProvider;
        $this->salesDataProvider = $salesDataProvider;
        $this->moneyFormatter = $moneyFormatter;
    }


    public function getRawData(ChannelInterface $channel, \DateTime $startDate, \DateTime $endDate, string $interval): array
    {
        /** @var DashboardStatisticsProviderInterface */
        $statistics = $this->statisticsProvider->getStatisticsForChannelInPeriod($channel, $startDate, $endDate);

        $salesSummary = $this->salesDataProvider->getSalesSummary(
            $channel,
            $startDate,
            $endDate,
            Interval::{$interval}()
        );

        /** @var string */
        $currencyCode = $channel->getBaseCurrency()->getCode();

        return [
            'sales_summary' => [
                'months' => $salesSummary->getIntervals(),
                'sales' => $salesSummary->getSales()
            ],
            'channel' => [
                'base_currency_code' => $currencyCode,
                'channel_code' => $channel->getCode(),
            ],
            'statistics'=>[
                'total_sales' => $this->moneyFormatter->format($statistics->getTotalSales(), $currencyCode),
                'number_of_new_orders' => $statistics->getNumberOfNewOrders(),
                'number_of_new_customers' => $statistics->getNumberOfNewCustomers(),
                'average_order_value' => $this->moneyFormatter->format($statistics->getAverageOrderValue(), $currencyCode),
            ]
        ];
    }
}
