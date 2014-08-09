<?php

namespace _64FF00\xPermissions;

use _64FF00\xPermissions\data\Group;
use _64FF00\xPermissions\data\User;

use pocketmine\level\Level;

use pocketmine\OfflinePlayer;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

class xPermissions extends PluginBase
{
	private $attachments = [];
	
	private $config, $groups;
	
	public function onEnable()
	{			
		$this->loadAll();
		
		$this->getCommand("xperms")->setExecutor(new Commands($this));
		
		$this->getServer()->getPluginManager()->registerEvents(new xListener($this), $this);
	}

	public function getAllGroups()
	{
		$result = [];
		
		foreach($this->groups->getAll(true) as $groupName)
		{
			array_push($result, new Group($this, $groupName));
		}
		
		return $result;
	}
	
	public function getConfiguration()
	{
		return $this->config;
	}
	
	public function getCustomConfig($fileName)
	{
		if(file_exists($this->getDataFolder() . $fileName))
		{
			return new Config($this->getDataFolder() . $fileName, Config::YAML, array());
		}
		
		return null;
	}
	
	public function getDefaultGroup()
	{
		foreach($this->getAllGroups() as $group)
		{
			if($group->isDefault()) return $group;
		}
		
		return null;
	}
	
	public function getGroup($groupName)
	{
		foreach($this->getAllGroups() as $group)
		{
			if(strtolower($group->getName()) == strtolower($groupName) || strtolower($group->getAlias()) == strtolower($groupName)) return $group;
		}
		
		return null;
	}
	
	public function getGroupsData()
	{
		return $this->groups->getAll();
	}
	
	public function getPermissions(Level $level, User $user)
	{
		return array_merge($user->getUserGroup($level)->getGroupPermissions($level), $user->getUserPermissions($level));
	}
	
	public function getValidPlayer($userName)
	{
		$player = $this->getServer()->getPlayer($userName);

		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($userName);
	}
	
	public function getUser($userName)
	{
		return new User($this, $this->getValidPlayer($userName));
	}
	
	public function isNegativeNode($permission)
	{
		return substr($permission, 1) === "-";
	}
	
	public function isValidPermission($permission)
	{
	}
	
	public function isValidRegExp($pattern)
	{
		return preg_match($pattern, null) === false;
	}
	
	public function loadAll()
	{
		@mkdir($this->getDataFolder() . "players/", 0777, true);
		
		$this->config = new Configuration($this);
		
		$this->loadGroupsConfig();
		
		$this->recalculatePlayerPermissions();
	}
	
	public function loadGroupsConfig()
	{
		if(!(file_exists($this->getDataFolder() . "groups.yml")))
		{
			$this->saveResource("groups.yml");
		}
		
		$this->groups = new Config($this->getDataFolder() . "groups.yml", Config::YAML, array(
		));
	}
	
	public function recalculatePlayerPermissions()
	{
		foreach($this->getServer()->getLevels() as $level)
		{
			foreach($this->getServer()->getOnlinePlayers() as $player)
			{
				$this->setPermissions($level, $this->getUser($player->getName()));	
			}
		}
	}
	
	public function setGroup(Level $level, Group $group, $player)
	{
		$this->getUser($player->getName())->setUserGroup($level, $group);
	}
	
	public function setGroupsData($temp_config)
	{
		if(is_array($temp_config))
		{
			$this->groups->setAll($temp_config);
			
			$this->groups->save();
		}
	}
	
	public function setPermissions(Level $level, User $user)
	{
		if($user->getPlayer() instanceof Player)
		{			
			$attachment = $user->getAttachment();
		
			foreach(array_keys($attachment->getPermissions()) as $old_permission)
			{
				$attachment->unsetPermission($old_permission);
			}	

			foreach($this->getPermissions($level, $user) as $permission)
			{
				if(!$this->isNegativeNode($permission))
				{
					$attachment->setPermission($permission, true);
				}
				else
				{
					$attachment->setPermission($permission, false);
				}
			}
			
			$user->getPlayer()->recalculatePermissions();
		}
	}
}