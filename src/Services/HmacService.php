<?php
declare(strict_types = 1);


namespace Patrikap\Hmac\Services;


use DateTime;
use Exception;
use Illuminate\Http\Request;
use Patrikap\Hmac\Exceptions\HmacException;
use function hash;
use function microtime;

/**
 * Class HmacService
 * @package Patrikap\Hmac\Services
 *
 * EN: Service for work with HMAC signed request
 * RU: Сервис для работы с HMAC подписями запросов
 *
 * @date 08.05.2020 22:14
 * @author Patrikap
 */
class HmacService
{
    /** @var string название переменной окружения для приватного ключа */
    public const    PRIVATE_KEY_ENV = 'HMAC_PRIVATE_KEY';

    /** @var string разделитель подписи и метки времени */
    protected const SIGNATURE_DELIMITER = ':';

    /** @var string формат даты из подписи */
    protected const DATE_FORMAT = 'U';

    /** @var string алгоритм кодирования */
    protected string $algo = 'md2';

    /** @var string|null приватный ключ */
    protected ?string $privateKey = null;

    /** @var int время жизни подписи */
    protected int $liveTime;

    /** @var string название поля подписи */
    protected string $field;


    /**
     * HmacService constructor.
     */
    public function __construct()
    {
        $this->algo = config('hmac.algo');
        $this->privateKey = config('hmac.key');
        $this->liveTime = config('hmac.ttl');
        $this->field = config('hmac.field');
    }


    /**
     * Хеширует сообщение
     *
     * @param string $message
     * @return string
     */
    protected function hash(string $message): string
    {
        return hash($this->algo, $message);
    }

    /**
     * Получает подпись из запроса тело/заголовки
     * подпись содержит в себе метку времени
     *
     * @param Request $request
     * @return string|null
     */
    protected function getSignatureFromRequest(Request $request): ?string
    {
        if ($signature = $request->get($this->field, null)) {
            return $signature;
        }
        if ($signature = $request->header($this->field, null)) {
            return $signature;
        }

        return null;
    }

    /**
     * Подписывает сообщение
     *
     * @param string $message
     * @return string
     * @throws HmacException
     */
    protected function signMessage(string $message): string
    {
        return $this->hash($this->getPrivateKey() . $message);
    }

    /**
     * Возвращает сгенерированную подпись на основе текущей метки времени
     *
     * @return string
     * @throws HmacException
     */
    public function makeSignature(): string
    {
        $time = (new DateTime())->format(self::DATE_FORMAT);
        $signature = $this->signMessage($time);

        return $signature . self::SIGNATURE_DELIMITER . $time;
    }

    /**
     * Метод проверки подписи запроса
     *
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function checkSignature(Request $request): bool
    {
        $signatureField = $this->getSignatureFromRequest($request);
        if (!$signatureField) {
            return false;
        }
        // В данной реализации хэш и временная метка подписи приходят в одном поле через разделитель
        $signatureChunks = explode(static::SIGNATURE_DELIMITER, $signatureField);
        $signature = $signatureChunks[0] ?? null;
        $date = $signatureChunks[1] ?? null;
        if (!$signature || !$date) {
            return false;
        }
        // Преобразовываем дату
        if (!$time = DateTime::createFromFormat('!' . self::DATE_FORMAT, $date)) {
            return false;
        }
        // Сверяем с разрешённым временем жизни
        if (abs(time() - $time->getTimestamp()) >= $this->liveTime) {
            return false;
        }

        // сравниваем подписи
        return strcmp($signature, $this->signMessage($date)) === 0;
    }

    /**
     * Возвращает текущий приватный ключ
     *
     * @return string|null
     * @throws HmacException
     */
    public function getPrivateKey(): ?string
    {
        if (is_null($this->privateKey)) {
            throw new HmacException('Private key for HMAC service has been empty, please generate key.');
        }

        return $this->privateKey;
    }

    /**
     * Генерирует приватный ключ
     *
     * @return $this
     */
    public function generatePrivateKey(): self
    {
        $this->privateKey = $this->algo . ':' . $this->hash((string)microtime());

        return $this;
    }
}
