<?php

namespace softmine\event\player;

use softmine\event\Cancellable;
use softmine\Player;

class PlayerToggleSprintEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var bool */
	protected $isSprinting;

	public function __construct(Player $player, $isSprinting){
		$this->player = $player;
		$this->isSprinting = (bool) $isSprinting;
	}

	public function isSprinting(){
		return $this->isSprinting;
	}

}