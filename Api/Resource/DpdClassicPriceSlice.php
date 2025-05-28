<?php

namespace DpdClassic\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use DpdClassic\Model\Map\DpdClassicPriceSliceTableMap;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Serializer\Annotation\Ignore;
use Thelia\Api\Bridge\Propel\State\PropelCollectionProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\PropelResourceTrait;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/admin/dpd-price-slices',
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]],
    provider: PropelCollectionProvider::class
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/front/dpd-price-slices',
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_FRONT_READ]],
    provider: PropelCollectionProvider::class
)]

class DpdClassicPriceSlice implements PropelResourceInterface
{

    use PropelResourceTrait;

    public const GROUP_ADMIN_READ = 'admin:dpd-price-slice:read';
    public const GROUP_FRONT_READ = 'front:dpd-price-slice:read';

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public int $id;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public int $areaId;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public float $maxWeight;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public float $price;

    #[Groups([self::GROUP_ADMIN_READ])]
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ])]
    public function getAreaId(): int
    {
        return $this->areaId;
    }

    public function setAreaId(int $areaId): self
    {
        $this->areaId = $areaId;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ])]
    public function getMaxWeight(): float
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(float $maxWeight): self
    {
        $this->maxWeight = $maxWeight;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ])]
    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @throws PropelException
     */
    #[Ignore] public static function getPropelRelatedTableMap(): ?TableMap
    {
        return DpdClassicPriceSliceTableMap::getTableMap();
    }
}
