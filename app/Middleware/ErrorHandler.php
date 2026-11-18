<?php

namespace App\Middleware;

use App\Core\Response;
use App\Exceptions\NotFoundException;
use InvalidArgumentException;
use PDOException;
use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(
            function (Throwable $e): void {
                // Detail error hanya ke log server
                error_log((string) $e);

                if ($e instanceof NotFoundException) {
                    Response::error($e->getMessage(), 404);
                }

                if ($e instanceof InvalidArgumentException) {
                    Response::error($e->getMessage(), 422);
                }

                // Pelanggaran constraint (mis. foreign key)
                if (
                    $e instanceof PDOException &&
                    ($e->errorInfo[0] ?? '') === '23000'
                ) {
                    Response::error(
                        'Data tidak dapat diproses karena masih '
                        . 'berelasi dengan data lain',
                        409
                    );
                }

                Response::error(
                    'Terjadi kesalahan internal server',
                    500
                );
            }
        );
    }
}
