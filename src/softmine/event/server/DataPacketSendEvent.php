<?php


namespace softmine\event\server;

use softmine\event;
use softmine\event\Cancellable;
use softmine\network\protocol\DataPacket;
use softmine\Player;

class DataPacketSendEvent extends ServerEvent implements Cancellable{
	public static $handlerList = null;

	private $packet;
	private $player;

	public function __construct(Player $player, DataPacket $packet){
		$this->packet = $packet;
		$this->player = $player;
	}

	public function getPacket(){
		return $this->packet;
	}

	public function getPlayer(){
		return $this->player;
	}
}