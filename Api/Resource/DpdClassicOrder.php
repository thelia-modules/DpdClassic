<?php


namespace DpdClassic\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DpdClassic\Api\State\DpdClassicOrderProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/admin/dpd-orders'
        ),
        new Get(
            uriTemplate: '/admin/dpd-orders/{id}',
            normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]]
        )
    ],
    normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]],
    provider: DpdClassicOrderProvider::class
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/front/dpd-orders'
        ),
        new Get(
            uriTemplate: '/front/dpd-orders/{id}',
            normalizationContext: ['groups' => [self::GROUP_FRONT_READ]]
        )
    ],
    normalizationContext: ['groups' => [self::GROUP_FRONT_READ]],
    provider: DpdClassicOrderProvider::class
)]
class DpdClassicOrder
{
    public const GROUP_ADMIN_READ = 'admin:dpd-order:read';
    public const GROUP_FRONT_READ = 'front:dpd-order:read';

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public int $id;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public string $ref;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public string $status;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public string $customerId;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public function getRef(): string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }
}
