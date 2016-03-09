<?php

/**
 * Player-only related events
 */
namespace softmine\event\player;

use softmine\event\Event;

abstract class PlayerEvent extends Event{
	/** @var \pocketmine\Player */
	protected $player;

	public function getPlayer(){
		return $this->player;
	}
}