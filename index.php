<?php
require_once(realpath(dirname(__FILE__)) . '/config/App.php');
App::init();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Account</title>
</head>
<body>
<div>
    <div>
        <?php
        $account = new Account(new AccountModel(), new CompositionModel());
        ?>
        <?php if ($account->findOne()): ?>
            <h4>1) Получить или установить поля "Номер", "Статус", "Дата", "Скидка"</h4>
            <ul>
                <li>Номер: <?= $account->getNumber(); ?></li>
                <li>Статус: <?= $account->getStatus(); ?></li>
                <li>Дата: <?= $account->date; ?></li>
                <li>Скидка: <?= $account->discount; ?></li>
            </ul>
        <?php endif; ?>
        <?php
        $account->setNumber($account->getNumber() + 1);
        $account->setStatus(array_rand(AccountModel::STATUSES));
        $account->date = date('Y-m-d H:i:s', time());
        $account->discount += 10;
        $account->update();

        ?>
        Обновление:
        <?php
        $account = new Account(new AccountModel(), new CompositionModel());
        ?>
        <?php if ($account->findOne()): ?>
            <ul>
                <li>Номер: <?= $account->getNumber(); ?></li>
                <li>Статус: <?= $account->getStatus(); ?></li>
                <li>Дата: <?= $account->date; ?></li>
                <li>Скидка: <?= $account->discount; ?></li>
            </ul>
        <?php endif; ?>
    </div>
    <hr/>
    <div>
        <h4>2) Добавить или удалить позицию в счете</h4>
        <?php
        $account = new Account(new AccountModel(), new CompositionModel());
        $account->setNumber(44441);
        $account->date = date('Y-m-d H:i:s', time());
        $account->addNew();

        // Проверяем
        $account = new Account(new AccountModel(), new CompositionModel());
        ?>
        <?php if ($account->findOne(['number' => 44441])): ?>
            <ul>
                <li>Номер: <?= $account->getNumber(); ?></li>
                <li>Статус: <?= $account->getStatus(); ?></li>
                <li>Дата: <?= $account->date; ?></li>
                <li>Скидка: <?= $account->discount; ?></li>
            </ul>
        <?php endif; ?>
        <?php
        // Удаление:
        $account->deleteAccount();
        ?>
    </div>
    <hr/>
    <div>
        <h4>3) Получить сумму счета на основе его состава и скидки</h4>
        <?php
        $account = new Account(new AccountModel(), new CompositionModel());
        $account->findOne();
        echo $account->total();
        ?>
    </div>
    </hr>
    <div>
        <h4>Получение счетов из БД в статусе "Оплачен" и с датой больше 01.01.2020</h4>
        <?php
        // наполняем базу тестовыми данныи
        for ($i = 1; $i < 100; $i++) {
            $account = new Account(new AccountModel(), new CompositionModel());
            $account->setNumber(rand(1, 10000));
            $account->setStatus(array_rand(AccountModel::STATUSES));
            $account->date = date(rand(10, 20) . '-' . rand(1, 12) . '-' . rand(1, 20) . ' H:i:s', time());
            $account->addNew();
            $composition = new CompositionModel();
            $composition->setAttributes([
                'accountId' => $account->getId(),
                'name' => 'Позиция-' . $i,
                'amount' => rand(10, 1000),
                'quantity' => rand(1, 20),
            ]);
            $composition->insert();
        }
        // Поиск
        $account = new Account(new AccountModel(), new CompositionModel());
        $accounts = $account->getPaid();
        ?>
        <pre>
            <?php print_r($accounts); ?>
        </pre>

        // Для оптимизации можно добавить составной ключ:
        CREATE INDEX status_date ON account(status, date)
    </div>
</div>
</body>
</html>