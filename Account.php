<?php

/**
 * Class Account - Счёт
 * @property int $number
 * @property int $status
 * @property string $date
 * @property int $discount
 * @property CompositionModel $composition
 *
 */
class Account
{
    /**
     * @var int
     */
    public $number;
    /**
     * @var int
     */
    private $status;
    /**
     * @var string
     */
    public $date;
    /**
     * @var int
     */
    public $discount;
    /**
     * @var array
     */
    protected $composition;

    /**
     * @var AccountModel
     */
    private $accountModel;
    /**
     * @var CompositionModel
     */
    private $compositionModel;

    function __construct(AccountModel $accountModel, CompositionModel $compositionModel)
    {
        $this->accountModel = $accountModel;
        $this->compositionModel = $compositionModel;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return (int)$this->number;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->status;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    public function __get($name)
    {
        if ($name === 'composition'){
            return $this->getCompositions();
        }
    }

    /**
     * Find one row
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function findOne(array $params = []): bool
    {
        /** @var AccountModel $accountModel */
        $accountModel = $this->accountModel::findOne($params);
        if ($accountModel) {
            $this->accountModel = $accountModel;
            if ($this->populate()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проставляем значения
     * @return bool
     */
    public function populate(): bool
    {
        $result = false;
        $class = self::class();
        foreach ($this->accountModel->attributes() as $attribute) {
            if (property_exists($class, $attribute)) {
                $result = true;
                $this->$attribute = $this->accountModel->$attribute;
            }
        }
        return $result;
    }

    /**
     * Model name
     * @return string
     */
    public function class()
    {
        return get_called_class();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        $primaryKey = $this->accountModel->primaryKey();
        return $this->accountModel->$primaryKey;
    }

    /**
     * Обновление счёта
     * @return bool
     * @throws Exception
     */
    public function update(): bool
    {
        $this->updateModelVariables();
        $this->accountModel->updatedAt = date('Y-m-d H:i:s');
        return (bool)$this->accountModel->update();
    }

    /**
     * Добавление счёта
     * @return bool
     * @throws Exception
     */
    public function addNew()
    {
        $this->updateModelVariables();
        $id = $this->accountModel->insert();
        if ($id){
            $primaryKey = $this->accountModel->primaryKey();
            $this->accountModel->$primaryKey = $id;
            return true;
        }
        return false;

    }

    /**
     * Обновление переменных модели
     */
    public function updateModelVariables(): void
    {
        foreach ($this->accountModel->attributes() as $attribute) {
            if (property_exists($this->class(), $attribute)) {
                $this->accountModel->$attribute = $this->$attribute;
            }
        }
    }

    /**
     * Удаление счёта
     * @return bool
     * @throws Exception
     */
    public function deleteAccount(): bool
    {
        return (bool)$this->accountModel->delete();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getCompositions(): array
    {
        return $this->compositionModel::findAll(['accountId' => $this->getId()]);
    }

    /**
     * Получить сумму счета на основе его состава и скидки
     * @return int
     * @throws Exception
     */
    public function total(): int
    {
        $sum = 0;

        $compositions = $this->getCompositions();
        foreach ($compositions as $composition) {
            /** @var CompositionModel $composition */
            $sum += $composition->amount * $composition->quantity;
        }

        if ($this->discount) {
            $sum -= $this->discount;
        }

        return $sum > 0 ? $sum : 0;
    }

    /**
     * Получение счетов из БД в статусе "Оплачен" и с датой больше 01.01.2020
     * @return array
     * @throws Exception
     */
    public function getPaid(): array
    {
        $accountTable = $this->accountModel::tableName();
        $compositionTable = $this->compositionModel::tableName();
        $payed = $this->accountModel::STATUS_ACCEPT;
        $query = "SELECT {$accountTable}.id, {$accountTable}.number, {$accountTable}.status, {$accountTable}.date,"
            . " {$accountTable}.discount,"
            . " {$compositionTable}.name, {$compositionTable}.amount, {$compositionTable}.quantity"
            . " FROM {$accountTable} "
            . " LEFT JOIN {$compositionTable} ON {$compositionTable}.accountId = {$accountTable}.id "
            . " WHERE {$accountTable}.status = {$payed} AND {$accountTable}.date > '2020-01-01 00:00:00'";
        return $this->accountModel::findQuery($query);
    }
}