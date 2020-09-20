<?php

/**
 * Class CompositionModel - Состав счёта
 * @property int $accountId
 * @property string $name Название
 * @property int $amount Сумма
 * @property int $quantity Колличество
 * @property string $createdAt Дата создания записи
 * @property string $updatedAt Дата обновления записи
 */
class CompositionModel extends Model
{
    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return 'composition';
    }

    /**
     * @inheritDoc
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return ['name', 'amount', 'quantity', 'accountId', 'createdAt', 'updatedAt'];
    }

}