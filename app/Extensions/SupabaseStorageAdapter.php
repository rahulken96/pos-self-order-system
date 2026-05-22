<?php

namespace App\Extensions;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use Illuminate\Support\Facades\Http;

class SupabaseStorageAdapter implements FilesystemAdapter
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct($url, $key, $bucket)
    {
        $this->url = rtrim($url, '/');
        $this->key = $key;
        $this->bucket = $bucket;
    }

    protected function getFullUrl($path)
    {
        return "{$this->url}/storage/v1/object/{$this->bucket}/{$path}";
    }

    protected function getHeaders()
    {
        return [
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
        ];
    }

    public function fileExists(string $path): bool
    {
        $response = Http::withHeaders($this->getHeaders())
            ->head($this->getFullUrl($path));
        return $response->successful();
    }

    public function directoryExists(string $path): bool
    {
        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        // Try uploading. If exists, we try upsert header if needed, or simple POST.
        // For Supabase, a normal upload is POST. If we want overwrite, we send x-upsert: true header.
        $response = Http::withHeaders(array_merge($this->getHeaders(), [
            'x-upsert' => 'true'
        ]))
        ->withBody($contents, 'application/octet-stream')
        ->post($this->getFullUrl($path));

        if (!$response->successful()) {
            throw new \Exception('Failed to upload file to Supabase: ' . $response->body());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    public function read(string $path): string
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->getFullUrl($path));
        return $response->body();
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->read($path));
        rewind($stream);
        return $stream;
    }

    public function delete(string $path): void
    {
        Http::withHeaders($this->getHeaders())
            ->delete($this->getFullUrl($path));
    }

    public function deleteDirectory(string $path): void
    {
        // Object storage doesn't require directory deletion
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Object storage directories are virtual
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Not implemented
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path, null, null, null, 'application/octet-stream');
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return [];
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->write($destination, $this->read($source), $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->write($destination, $this->read($source), $config);
    }

    public function getUrl(string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
    }
}
