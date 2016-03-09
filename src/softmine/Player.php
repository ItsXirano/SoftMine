<?php

namespace softmine;

use softmine\block\Block;
use softmine\command\CommandSender;

//Entity
use softmine\entity\Arrow;
use softmine\entity\Effect;
use softmine\entity\Entity;
use softmine\entity\Human;
use softmine\entity\Item as DroppedItem;
use softmine\entity\Living;
use softmine\entity\Projectile;

//Events
use softmine\event\block\SignChangeEvent;
use softmine\event\entity\EntityDamageByBlockEvent;
use softmine\event\entity\EntityDamageByEntityEvent;
use softmine\event\entity\EntityDamageEvent;
use softmine\event\entity\EntityRegainHealthEvent;
use softmine\event\entity\EntityShootBowEvent;
use softmine\event\entity\ProjectileLaunchEvent;
use softmine\event\inventory\CraftItemEvent;
use softmine\event\inventory\InventoryCloseEvent;
use softmine\event\inventory\InventoryPickupArrowEvent;
use softmine\event\inventory\InventoryPickupItemEvent;
use softmine\event\player\PlayerAchievementAwardedEvent;
use softmine\event\player\PlayerAnimationEvent;
use softmine\event\player\PlayerBedEnterEvent;
use softmine\event\player\PlayerBedLeaveEvent;
use softmine\event\player\PlayerChatEvent;
use softmine\event\player\PlayerCommandPreprocessEvent;
use softmine\event\player\PlayerDeathEvent;
use softmine\event\player\PlayerDropItemEvent;
use softmine\event\player\PlayerGameModeChangeEvent;
use softmine\event\player\PlayerInteractEvent;
use softmine\event\player\PlayerItemConsumeEvent;
use softmine\event\player\PlayerJoinEvent;
use softmine\event\player\PlayerKickEvent;
use softmine\event\player\PlayerLoginEvent;
use softmine\event\player\PlayerMoveEvent;
use softmine\event\player\PlayerPreLoginEvent;
use softmine\event\player\PlayerQuitEvent;
use softmine\event\player\PlayerRespawnEvent;
use softmine\event\player\PlayerToggleSneakEvent;
use softmine\event\player\PlayerToggleSprintEvent;
use softmine\event\server\DataPacketReceiveEvent;
use softmine\event\server\DataPacketSendEvent;
use softmine\event\TextContainer;
use softmine\event\Timings;
use softmine\event\TranslationContainer;

//Inventory
use softmine\inventory\BaseTransaction;
use softmine\inventory\BigShapedRecipe;
use softmine\inventory\BigShapelessRecipe;
use softmine\inventory\FurnaceInventory;
use softmine\inventory\Inventory;
use softmine\inventory\InventoryHolder;
use softmine\inventory\PlayerInventory;
use softmine\inventory\ShapedRecipe;
use softmine\inventory\ShapelessRecipe;
use softmine\inventory\SimpleTransactionGroup;

use softmine\item\Item;

//Level
use softmine\level\ChunkLoader;
use softmine\level\format\FullChunk;
use softmine\level\format\LevelProvider;
use softmine\level\Level;
use softmine\level\Location;
use softmine\level\Position;
use softmine\level\sound\LaunchSound;

//Math
use softmine\math\AxisAlignedBB;
use softmine\math\Vector2;
use softmine\math\Vector3;

use softmine\metadata\MetadataValue;

//NBT
use softmine\nbt\NBT;
use softmine\nbt\tag\Byte;
use softmine\nbt\tag\Compound;
use softmine\nbt\tag\Double;
use softmine\nbt\tag\Enum;
use softmine\nbt\tag\Float;
use softmine\nbt\tag\Int;
use softmine\nbt\tag\Long;
use softmine\nbt\tag\Short;
use softmine\nbt\tag\String;

//Protocol Services
use softmine\network\Network;
use softmine\network\protocol\AdventureSettingsPacket;
use softmine\network\protocol\AnimatePacket;
use softmine\network\protocol\BatchPacket;
use softmine\network\protocol\ContainerClosePacket;
use softmine\network\protocol\ContainerSetContentPacket;
use softmine\network\protocol\DataPacket;
use softmine\network\protocol\DisconnectPacket;
use softmine\network\protocol\EntityEventPacket;
use softmine\network\protocol\FullChunkDataPacket;
use softmine\network\protocol\Info as ProtocolInfo;
use softmine\network\protocol\PlayerActionPacket;
use softmine\network\protocol\PlayStatusPacket;
use softmine\network\protocol\RespawnPacket;
use softmine\network\protocol\SetPlayerGameTypePacket;
use softmine\network\protocol\TextPacket;
use softmine\network\protocol\MovePlayerPacket;
use softmine\network\protocol\SetDifficultyPacket;
use softmine\network\protocol\SetEntityMotionPacket;
use softmine\network\protocol\SetHealthPacket;
use softmine\network\protocol\SetSpawnPositionPacket;
use softmine\network\protocol\SetTimePacket;
use softmine\network\protocol\StartGamePacket;
use softmine\network\protocol\TakeItemEntityPacket;
use softmine\network\protocol\UpdateBlockPacket;
use softmine\network\SourceInterface;

//Permissions
use softmine\permission\PermissibleBase;
use softmine\permission\PermissionAttachment;

//Others
use softmine\plugin\Plugin;
use softmine\tile\Sign;
use softmine\tile\Spawnable;
use softmine\tile\Tile;
use softmine\utils\TextFormat;

//RakLib
use raklib\Binary;

class Player extends Human implements CommandSender, InventoryHolder, ChunkLoader, IPlayer{

	const SURVIVAL = 0;
	const CREATIVE = 1;
	const ADVENTURE = 2;
	const SPECTATOR = 3;
	const VIEW = Player::SPECTATOR;

	const SURVIVAL_SLOTS = 36;
	const CREATIVE_SLOTS = 112;

	/** @var SourceInterface */
	protected $interface;

	public $spawned = false;
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;

	protected $windowCnt = 2;
	/** @var \SplObjectStorage<Inventory> */
	protected $windows;
	/** @var Inventory[] */
	protected $windowIndex = [];

	protected $messageCounter = 2;

	protected $sendIndex = 0;

	private $clientSecret;

	/** @var Vector3 */
	public $speed = null;

	public $blocked = false;
	public $achievements = [];
	public $lastCorrect;
	/** @var SimpleTransactionGroup */
	protected $currentTransaction = null;
	public $craftingType = 0; //0 = 2x2 crafting, 1 = 3x3 crafting, 2 = stonecutter

	protected $isCrafting = false;

	/**
	 * @deprecated
	 * @var array
	 */
	public $loginData = [];

	public $creationTime = 0;

	protected $randomClientId;

	protected $lastMovement = 0;
	/** @var Vector3 */
	protected $forceMovement = null;
	/** @var Vector3 */
	protected $teleportPosition = null;
	protected $connected = true;
	protected $ip;
	protected $removeFormat = true;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $startAction = -1;
	/** @var Vector3 */
	protected $sleeping = null;
	protected $clientID = null;

	private $loaderId = null;

	protected $stepHeight = 0.6;

	public $usedChunks = [];
	protected $chunkLoadCount = 0;
	protected $loadQueue = [];
	protected $nextChunkOrderRun = 5;

	/** @var Player[] */
	protected $hiddenPlayers = [];

	/** @var Vector3 */
	protected $newPosition;

	protected $viewDistance;
	protected $chunksPerTick;
    protected $spawnThreshold;
	/** @var null|Position */
	private $spawnPosition = null;

	protected $inAirTicks = 0;
	protected $startAirTicks = 5;

	protected $autoJump = true;

	protected $allowFlight = false;

	private $needACK = [];

	private $batchedPackets = [];

	/** @var PermissibleBase */
	private $perm = null;

	public function getLeaveMessage(){
		return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
			$this->getDisplayName()
		]);
	}
	}
