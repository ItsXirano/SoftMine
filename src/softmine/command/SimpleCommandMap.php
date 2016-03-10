<?php


namespace softmine\command;

use softmine\command\defaults\BanCommand;
use softmine\command\defaults\BanIpCommand;
use softmine\command\defaults\BanListCommand;
use softmine\command\defaults\DefaultGamemodeCommand;
use softmine\command\defaults\DeopCommand;
use softmine\command\defaults\DifficultyCommand;
use softmine\command\defaults\DumpMemoryCommand;
use softmine\command\defaults\EffectCommand;
use softmine\command\defaults\EnchantCommand;
use softmine\command\defaults\GamemodeCommand;
use softmine\command\defaults\GarbageCollectorCommand;
use softmine\command\defaults\GiveCommand;
use softmine\command\defaults\HelpCommand;
use softmine\command\defaults\KickCommand;
use softmine\command\defaults\KillCommand;
use softmine\command\defaults\ListCommand;
use softmine\command\defaults\MeCommand;
use softmine\command\defaults\OpCommand;
use softmine\command\defaults\PardonCommand;
use softmine\command\defaults\PardonIpCommand;
use softmine\command\defaults\ParticleCommand;
use softmine\command\defaults\PluginsCommand;
use softmine\command\defaults\ReloadCommand;
use softmine\command\defaults\SaveCommand;
use softmine\command\defaults\SaveOffCommand;
use softmine\command\defaults\SaveOnCommand;
use softmine\command\defaults\SayCommand;
use softmine\command\defaults\SeedCommand;
use softmine\command\defaults\SetWorldSpawnCommand;
use softmine\command\defaults\SpawnpointCommand;
use softmine\command\defaults\StatusCommand;
use softmine\command\defaults\StopCommand;
use softmine\command\defaults\TeleportCommand;
use softmine\command\defaults\TellCommand;
use softmine\command\defaults\TimeCommand;
use softmine\command\defaults\TimingsCommand;
use softmine\command\defaults\VanillaCommand;
use softmine\command\defaults\VersionCommand;
use softmine\command\defaults\WhitelistCommand;
use softmine\event\TranslationContainer;
use softmine\Server;
use softmine\utils\MainLogger;
use softmine\utils\TextFormat;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var Command[]
	 */
	protected $knownCommands = [];

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
		$this->setDefaultCommands();
	}

	private function setDefaultCommands(){
		$this->register("softmine", new VersionCommand("version"));
		$this->register("softmine", new PluginsCommand("plugins"));
		$this->register("softmine", new SeedCommand("seed"));
		$this->register("softmine", new HelpCommand("help"));
		$this->register("softmine", new StopCommand("stop"));
		$this->register("softmine", new TellCommand("tell"));
		$this->register("softmine", new DefaultGamemodeCommand("defaultgamemode"));
		$this->register("softmine", new BanCommand("ban"));
		$this->register("softmine", new BanIpCommand("ban-ip"));
		$this->register("softmine", new BanListCommand("banlist"));
		$this->register("softmine", new PardonCommand("pardon"));
		$this->register("softmine", new PardonIpCommand("pardon-ip"));
		$this->register("softmine", new SayCommand("say"));
		$this->register("softmine", new MeCommand("me"));
		$this->register("softmine", new ListCommand("list"));
		$this->register("softmine", new DifficultyCommand("difficulty"));
		$this->register("softmine", new KickCommand("kick"));
		$this->register("softmine", new OpCommand("op"));
		$this->register("softmine", new DeopCommand("deop"));
		$this->register("softmine", new WhitelistCommand("whitelist"));
		$this->register("softmine", new SaveOnCommand("save-on"));
		$this->register("softmine", new SaveOffCommand("save-off"));
		$this->register("softmine", new SaveCommand("save-all"));
		$this->register("softmine", new GiveCommand("give"));
		$this->register("softmine", new EffectCommand("effect"));
		$this->register("softmine", new EnchantCommand("enchant"));
		$this->register("softmine", new ParticleCommand("particle"));
		$this->register("softmine", new GamemodeCommand("gamemode"));
		$this->register("softmine", new KillCommand("kill"));
		$this->register("softmine", new SpawnpointCommand("spawnpoint"));
		$this->register("softmine", new SetWorldSpawnCommand("setworldspawn"));
		$this->register("softmine", new TeleportCommand("tp"));
		$this->register("softmine", new TimeCommand("time"));
		$this->register("softmine", new TimingsCommand("timings"));
		$this->register("softmine", new ReloadCommand("reload"));

		if($this->server->getProperty("debug.commands", false)){
			$this->register("softmine", new StatusCommand("status"));
			$this->register("softmine", new GarbageCollectorCommand("gc"));
			$this->register("softmine", new DumpMemoryCommand("dumpmemory"));
		}
	}


	public function registerAll($fallbackPrefix, array $commands){
		foreach($commands as $command){
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register($fallbackPrefix, Command $command, $label = null){
		if($label === null){
			$label = $command->getName();
		}
		$label = strtolower(trim($label));
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach($aliases as $index => $alias){
			if(!$this->registerAlias($command, true, $fallbackPrefix, $alias)){
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if(!$registered){
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	private function registerAlias(Command $command, $isAlias, $fallbackPrefix, $label){
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if(($command instanceof VanillaCommand or $isAlias) and isset($this->knownCommands[$label])){
			return false;
		}

		if(isset($this->knownCommands[$label]) and $this->knownCommands[$label]->getLabel() !== null and $this->knownCommands[$label]->getLabel() === $label){
			return false;
		}

		if(!$isAlias){
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	public function dispatch(CommandSender $sender, $commandLine){
		$args = explode(" ", $commandLine);

		if(count($args) === 0){
			return false;
		}

		$sentCommandLabel = strtolower(array_shift($args));
		$target = $this->getCommand($sentCommandLabel);

		if($target === null){
			return false;
		}

		$target->timings->startTiming();
		try{
			$target->execute($sender, $sentCommandLabel, $args);
		}catch(\Throwable $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
			$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.command.exception", [$commandLine, (string) $target, $e->getMessage()]));
			$sender->getServer()->getLogger()->logException($e);
		}
		$target->timings->stopTiming();

		return true;
	}

	public function clearCommands(){
		foreach($this->knownCommands as $command){
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand($name){
		if(isset($this->knownCommands[$name])){
			return $this->knownCommands[$name];
		}

		return null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands(){
		return $this->knownCommands;
	}


	/**
	 * @return void
	 */
	public function registerServerAliases(){
		$values = $this->server->getCommandAliases();

		foreach($values as $alias => $commandStrings){
			if(strpos($alias, ":") !== false or strpos($alias, " ") !== false){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("softmine.command.alias.illegal", [$alias]));
				continue;
			}

			$targets = [];

			$bad = "";
			foreach($commandStrings as $commandString){
				$args = explode(" ", $commandString);
				$command = $this->getCommand($args[0]);

				if($command === null){
					if(strlen($bad) > 0){
						$bad .= ", ";
					}
					$bad .= $commandString;
				}else{
					$targets[] = $commandString;
				}
			}

			if(strlen($bad) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translateString("softmine.command.alias.notFound", [$alias, $bad]));
				continue;
			}

			//These registered commands have absolute priority
			if(count($targets) > 0){
				$this->knownCommands[strtolower($alias)] = new FormattedCommandAlias(strtolower($alias), $targets);
			}else{
				unset($this->knownCommands[strtolower($alias)]);
			}

		}
	}


}