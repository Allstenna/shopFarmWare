<?php
class Database {
    private static ?PDO $instance = null;
    
    // Настройки — замените на свои из панели Beget → MySQL
    private const HOST = 'localhost';
    private const DB_NAME = 'mart20xd_db';
    private const USER = 'mart20xd_db';
    private const PASS = 'tXr4kYkL!';
    private const CHARSET = 'utf8mb4';
    
    // 🔹 КРИТИЧНО: отключаем постоянные соединения для shared-hosting
    private const OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
    ];
    
    private function __construct() {}
    private function __clone() {}
    public function __wakeup(): void {
        throw new LogicException('Cannot unserialize ' . self::class);
    }
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST,
                self::DB_NAME,
                self::CHARSET
            );
            
            try {
                self::$instance = new PDO($dsn, self::USER, self::PASS, self::OPTIONS);
            } catch (PDOException $e) {
                // 🔹 Специальная обработка "Too many connections"
                if ($e->getCode() === 1040 || str_contains($e->getMessage(), 'Too many connections')) {
                    // Пробуем сбросить и переподключиться один раз
                    self::$instance = null;
                    usleep(100000); // Ждём 0.1 секунды
                    try {
                        self::$instance = new PDO($dsn, self::USER, self::PASS, self::OPTIONS);
                    } catch (PDOException $e2) {
                        error_log('DB: Too many connections after retry: ' . $e2->getMessage());
                        if (getenv('APP_ENV') !== 'production') {
                            http_response_code(503);
                            echo "<pre>🔴 Сервер перегружен. Попробуйте через 30 секунд.\nОшибка: " . $e2->getMessage() . "</pre>";
                        }
                        throw new PDOException('Слишком много подключений к БД. Попробуйте позже.');
                    }
                } else {
                    error_log('DB Connection Error: ' . $e->getMessage());
                    if (getenv('APP_ENV') !== 'production') {
                        echo "<pre>🔴 DB Error: " . htmlspecialchars($e->getMessage()) . "\nDSN: $dsn</pre>";
                    }
                    throw $e;
                }
            }
        }
        return self::$instance;
    }
    
    /**
     * Явно закрывает соединение (для длинных скриптов или при ошибках)
     */
    public static function close(): void {
        if (self::$instance !== null) {
            self::$instance = null; // PDO закроется автоматически при unset
        }
    }
    
    /**
     * Сброс для тестов
     */
    public static function resetInstance(): void {
        self::close();
    }
}