<?php
declare(strict_types=1);
namespace App\Core;

class Response {
    public function __construct(
        private string $body = '',
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function view(string $page, array $data = [], ?string $layout = 'public'): self
    {
        return new self(View::render($page, $data, $layout));
    }

    public static function json(array $payload, int $status = 200): self
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{"error":"json_encoding_error"}';
            $status = 500;
        }

        return new self($json, $status, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
