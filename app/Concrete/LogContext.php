<?php

namespace App\Concrete;

class LogContext
{
    public function __construct(
        public string $thrown,
        public bool $isException,
        public bool $isError,
        public string $message,
        public string $file,
        public string $line,
        public string $request,
    ){}
}
