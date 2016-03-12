<?php

namespace softmine\proxy\ProxyPC;

use softmine\network\NetWork;

use softmine\Achievement;
use softmine\Player;
use softminesoftmine\entity\Entity;
use softmine\item\Item;
use softmine\Block\Block;
use softmine\level\Level;
use softmine\network\protocol\AddEntityPacket;
use softmine\network\protocol\AddItemEntityPacket;
use softmine\network\protocol\AddPaintingPacket;
use softmine\network\protocol\AddPlayerPacket;
use softmine\network\protocol\AdventureSettingsPacket;
use softmine\network\protocol\AnimatePacket;
use softmine\network\protocol\ContainerClosePacket;
use softmine\network\protocol\ContainerOpenPacket;
use softmine\network\protocol\ContainerSetContentPacket;
use softmine\network\protocol\ContainerSetDataPacket;
use softmine\network\protocol\ContainerSetSlotPacket;
use softmine\network\protocol\CraftingDataPacket;
use softmine\network\protocol\CraftingEventPacket;
use softmine\network\protocol\DataPacket;
use softmine\network\protocol\DropItemPacket;
use softmine\network\protocol\FullChunkDataPacket;
use softmine\network\protocol\Info;
use softmine\network\protocol\SetEntityLinkPacket;
use softmine\network\protocol\TileEntityDataPacket;
use softmine\network\protocol\EntityEventPacket;
use softmine\network\protocol\ExplodePacket;
use softmine\network\protocol\HurtArmorPacket;
use softmine\network\protocol\InteractPacket;
use softmine\network\protocol\LevelEventPacket;
use softmine\network\protocol\DisconnectPacket;
use softmine\network\protocol\TextPacket;
use softmine\network\protocol\MoveEntityPacket;
use softmine\network\protocol\MovePlayerPacket;
use softmine\network\protocol\PlayerActionPacket;
use softmine\network\protocol\MobArmorEquipmentPacket;
use softmine\network\protocol\MobEquipmentPacket;
use softmine\network\protocol\RemoveBlockPacket;
use softmine\network\protocol\RemoveEntityPacket;
use softmine\network\protocol\RemovePlayerPacket;
use softmine\network\protocol\RespawnPacket;
use softmine\network\protocol\SetDifficultyPacket;
use softmine\network\protocol\SetEntityDataPacket;
use softmine\network\protocol\SetEntityMotionPacket;
use softmine\network\protocol\SetSpawnPositionPacket;
use softmine\network\protocol\TakeItemEntityPacket;
use softmine\network\protocol\TileEventPacket;
use softmine\network\protocol\UpdateBlockPacket;
use softmine\network\protocol\UseItemPacket;
use softmine\math\Vector3;
use softmine\nbt\NBT;
use softmine\tile\Tile;
use softmine\utils\TextFormat;

class ProxyPC extends NetWork{
  
	const VERSION = "1.9";
	const PROTOCOL = 47;
	
	const CURRENT_PROTOCOL = 47;
	const TARGET_PROTOCOL = 45;
	
	const CURRENT_MINECRAFT_VERSION_NETWORK = "1.9";
	
		public function onEnable(){
		$this->saveDefaultConfig();

		$port = (int) $this->getConfig()->get("port");
		if($port === $this->getServer()->getPort()){
			$this->getLogger()->error("In Config.yml edit Port");
			return;
		}

		if(Info::CURRENT_PROTOCOL !== self::TARGET_PROTOCOL){
			$this->getLogger()->error("Protocol is Error");
			return;
		}

		$this->getLogger()->info("Starting Minecraft PC server ".$this->getDescription()->getVersion()." on port $port");
		$interface = new NewInterface($this->getServer(), $port);
		$this->getServer()->getNetwork()->registerInterface($interface);
	}
}
