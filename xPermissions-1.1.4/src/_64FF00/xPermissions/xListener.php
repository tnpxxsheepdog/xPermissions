<?php

namespace _64FF00\xPermissions;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\Player;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class xListener implements Listener
{
	public function __construct(xPermissions $plugin)
	{
		$this->plugin = $plugin;
	}

	public function onLevelChange(EntityLevelChangeEvent $event)
	{		
		$player = $event->getEntity();
		
		$level = $event->getTarget();
		
		if($player instanceof Player)
		{
			$this->plugin->setPermissions($level, $player);
		}
	}
	
	public function onPlayerChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		
		$format = $this->plugin->getFormattedMessage($player, $event->getMessage());
		
		$config_node = $this->plugin->getConfiguration()->isFormatterEnabled();
		
		if(isset($config_node) and $config_node === true)
		{
			$event->setFormat($format);
		}
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		
		$this->plugin->setPermissions($player->getLevel(), $player);
	}

	public function onPlayerKick(PlayerKickEvent $event)
	{
		$player = $event->getPlayer();
		
		$this->plugin->removeAttachment($player);
	}

	public function onPlayerQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		
		$this->plugin->removeAttachment($player);
	}
}