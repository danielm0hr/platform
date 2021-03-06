<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Payment\DataAbstractionLayer;

use Shopware\Core\Checkout\Payment\PaymentEvents;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\Feature;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class PaymentHandlerIdentifierSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PaymentEvents::PAYMENT_METHOD_LOADED_EVENT => 'formatHandlerIdentifier',
        ];
    }

    public function formatHandlerIdentifier(EntityLoadedEvent $event): void
    {
        /** @var PaymentMethodEntity $entity */
        foreach ($event->getEntities() as $entity) {
            $explodedHandlerIdentifier = explode('\\', $entity->getHandlerIdentifier());

            if (Feature::isActive('FEATURE_NEXT_9351')) {
                $last = $explodedHandlerIdentifier[count($explodedHandlerIdentifier) - 1];
                $entity->setShortName((new CamelCaseToSnakeCaseNameConverter())->normalize((string) $last));
            }

            if (count($explodedHandlerIdentifier) < 2) {
                $entity->setFormattedHandlerIdentifier($entity->getHandlerIdentifier());

                continue;
            }

            $formattedHandlerIdentifier = 'handler_'
                . mb_strtolower(array_shift($explodedHandlerIdentifier))
                . '_'
                . mb_strtolower(array_pop($explodedHandlerIdentifier));

            $entity->setFormattedHandlerIdentifier($formattedHandlerIdentifier);
        }
    }
}
