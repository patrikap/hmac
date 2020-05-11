<?php
declare(strict_types = 1);


namespace Patrikap\Hmac\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Patrikap\Hmac\Services\HmacService;

/**
 * Class HmacGenerateCommand
 * @package Patrikap\Hmac\Console\Commands
 *
 * EN: Generate private key for HMAC authenticate
 * RU: Команда - генератор приватного ключа для HMAC авторизации
 *
 * @date 08.05.2020 22:31
 * @author Konstantin.K
 */
class HmacGenerateCommand extends Command
{
    use ConfirmableTrait;

    /** @var string название команды */
    protected $signature = 'hmac:generate';

    /** @var string Описание команды */
    protected $description = 'Set the HMAC private key';

    /** @var HmacService сервис хеширования */
    protected HmacService $hmacService;

    /**
     * Тело команды
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        // уверены ли мы, что ходим запускать это на продакшне
        if (!$this->confirmToProceed()) {
            return;
        }
        $this->hmacService = app()->make(HmacService::class);
        // получаем текущий ключ
        $oldKey = $this->hmacService->getPrivateKey();
        // генерируем ключ
        $key = $this->generateRandomKey();
        // записываем в файл
        $this->writeNewEnvironmentFileWith($oldKey, $key);
        // печатаем ключ
        $this->alert($key);
        // корректно завершаем работу скрипта
        $this->info('HMAC private key set successfully.');
    }


    /**
     * Переписывает в энв-файле значение переменной
     *
     * @param string $oldKey
     * @param string $newKey
     */
    protected function writeNewEnvironmentFileWith(string $oldKey, string $newKey): void
    {
        file_put_contents($this->laravel->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern($this->hmacService::PRIVATE_KEY_ENV, $oldKey),
            $this->hmacService::PRIVATE_KEY_ENV . '=' . $newKey,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    /**
     * Получает шаблон замены переменной
     *
     * @param string $paramName
     * @param string $key
     * @return string
     */
    protected function keyReplacementPattern(string $paramName, string $key): string
    {
        $escaped = preg_quote('=' . $key, '/');

        return "/^{$paramName}{$escaped}/m";
    }

    /**
     * генерирует произвольную строку на основе выбранного алгоритма
     *
     * @return string
     */
    protected function generateRandomKey(): string
    {
        return $this->hmacService->generatePrivateKey()->getPrivateKey();
    }
}
