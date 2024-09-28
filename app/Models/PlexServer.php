<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlexServer
{
	use HasFactory;

	private string $server;
	private string $token;
	private int $port;

	public function __construct(string $server, string $token, int $port)
	{
		$this->server = $server;
		$this->token = $token;
		$this->port = $port;
	}

	public function getServer(): string
	{
		return $this->server;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function getPort(): int
	{
		return $this->port;
	}
}
