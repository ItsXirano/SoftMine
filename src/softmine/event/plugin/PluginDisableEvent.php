<?php


namespace softmine\event\plugin;

use softmine\plugin\Plugin;


class PluginDisableEvent extends PluginEvent{
	public static $handlerList = null;

	/**
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin){
		parent::__construct($plugin);
	}
}
