<?php

/**
 * Class Account - Счёт
 * @property int $number Номер
 * @property int $status Статус
 * @property string $date Дата
 * @property int $discount Скидка
 * @property string $createdAt Дата создания записи
 * @property string $updatedAt Дата обновления записи
 */
class AccountModel extends Model
{
    const STATUS_NEW = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_DECLINE = 3;
    const STATUSES = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_ACCEPT => 'Оплачен',
        self::STATUS_DECLINE => 'Ошибочный',
    ];


    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return 'account';
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
        return ['number', 'status', 'date', 'discount', 'createdAt', 'updatedAt'];
    }

}