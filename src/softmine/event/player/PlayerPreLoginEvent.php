<?php

namespace softmine\event\player;

use softmine\event\Cancellable;
use softmine\Player;

/**
 * Called when the player logs in, before things have been set up
 */
class PlayerPreLoginEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var string */
	protected $kickMessage;

	public function __construct(Player $player, $kickMessage){
		$this->player = $player;
		$this->kickMessage = $kickMessage;
	}

	public function setKickMessage($kickMessage){
		$this->kickMessage = $kickMessage;
	}

	public function getKickMessage(){
		return $this->kickMessage;
	}

}