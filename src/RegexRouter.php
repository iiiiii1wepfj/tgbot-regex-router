<?php
declare(strict_types=1);

namespace KeythKatz\Bot;

class RegexRouter
{
	const REGEX_START = "/^(?i)";
	const REGEX_END = "$/";

	private $botNamePrefix;

	private $routes = [];

	public function __construct(string $botName)
	{
		$this->botNamePrefix = "($botName )";
	}

	/**
	 * Add a route to handle.
	 * @param string   $regex           Regex to handle without the start and end. Just the important bits.
	 * @param bool     $includeBotName  Whether to include the bot name in the regex in group chats.
	 *                                  Will be always optional in PMs and PM mode in group chats via handlePm().
	 * @param bool     $optionalBotName Whether the bot name is optional in group chats.
	 * @param callable $action          Closure to run when the regex is matched.
	 */
	public function addRoute(string $regex, bool $includeBotName, bool $optionalBotName, callable $action)
	{
		array_push($this->routes, (object)[
			"regex" => $regex,
			"includeBotName" => $includeBotName,
			"optionalBotName" => $optionalBotName,
			"action" => $action
		]);
	}

	/**
	 * Handles the text. If it matches, the linked closure is run.
	 * @param  string $text Text to match.
	 * @return bool       Whether there was a match.
	 */
	public function handle(string $text): bool
	{
		foreach ($this->routes as $route) {
			$regex = self::REGEX_START;
			if ($route->includeBotName) $regex .= $this->botNamePrefix;
			if ($route->includeBotName && $route->optionalBotName) $regex .= "*";
			$regex .= $route->regex . self::REGEX_END;

			if (preg_match($regex, $text)) {
				($route->action)();
				return true;
			}
		}

		return false;
	}

	public function handlePm(string $text): bool
	{
		foreach ($this->routes as $route) {
			$regex = self::REGEX_START;
			if ($route->includeBotName) $regex .= $this->botNamePrefix . "*";
			$regex .= $route->regex . self::REGEX_END;

			if (preg_match($regex, $text)) {
				($route->action)();
				return true;
			}
		}

		return false;
	}
}