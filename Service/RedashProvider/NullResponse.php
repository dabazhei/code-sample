<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use App\Exception\DashboardCreationException;

final class NullResponse extends Response
{
    /**
     * NullResponse constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        throw new DashboardCreationException('Дашборд не может быть создан, сервис вернул пустой ответ');
    }

    /**
     * @param string $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function get(string $key, $defaultValue = null)
    {
        if (null === $defaultValue) {
            throw new DashboardCreationException(
                "Дашборд не может быть создан, нет элемента с ключем {$key}, сервис вернул пустой ответ"
            );
        }

        return $defaultValue;
    }
}