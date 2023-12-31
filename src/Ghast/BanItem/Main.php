<?php

namespace Ghast\BanItem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\entity\EntityShootBowEvent;

class Main extends PluginBase implements Listener {

	public function onEnable() {
		$this->saveItems();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onTouch(PlayerInteractEvent $event) {
		$p = $event->getPlayer();
		if ($this->isBanned($event->getItem())) {
			if (!($p->hasPermission("banitem") || $p->hasPermission("banitem.bypass"))) {
				$p->sendMessage("§f[§cVật Phẩm Đã Bị Cấm§f]");
				$event->setCancelled();
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$p = $event->getPlayer();
		if ($this->isBanned($event->getItem())) {
			if (!($p->hasPermission("banitem") || $p->hasPermission("banitem.bypass"))) {
				$p->sendMessage("§f[§cVật Phẩm Đã Bị Cấm§f]");
				$event->setCancelled();
			}
		}
	}

	public function onHurt(EntityDamageEvent $event) {
		if ($event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player) {
			$p = $event->getDamager();
			if ($this->isBanned($p->getInventory()->getItemInHand())) {
				if (!($p->hasPermission("banitem") || $p->hasPermission("banitem.bypass"))) {
					$p->sendMessage("§f[§cVật Phẩm Đã Bị Cấm§f]");
					$event->setCancelled();
				}
			}
		}
	}

	public function onEat(PlayerItemConsumeEvent $event) {
		$p = $event->getPlayer();
		if ($this->isBanned($event->getItem())) {
			if (!($p->hasPermission("banitem") || $p->hasPermission("banitem.bypass"))) {
				$p->sendMessage("§f[§cVật Phẩm Đã Bị Cấm§f]");
				$event->setCancelled();
			}
		}
	}

	public function onShoot(EntityShootBowEvent $event) {
		if ($event->getEntity() instanceof Player) {
			$p = $event->getEntity();
			if ($this->isBanned($event->getBow())) {
				if (!($p->hasPermission("banitem") || $p->hasPermission("banitem.bypass"))) {
					$p->sendMessage("§f[§cVật Phẩm Đã Bị Cấm§f]");
					$event->setCancelled();
				}
			}
		}
	}

	public function onCommand(CommandSender $p, Command $cmd, $label, array $args) {
		if (!isset($args[0])) {
			return false;
		}
		if (strtolower($args[0]) == "ban" || strtolower($args[0]) == "unban") {
			if (!isset($args[1])) {
				return false;
			}
			$item = explode(":", $args[1]);
			if (!is_numeric($item[0]) || (isset($item[1]) && !is_numeric($item[1]))) {
				$p->sendMessage("§bVui Lòng Chỉ Sử Dụng ID Vật Phẩm Để Ban");
				return true;
			}
		}
		if (strtolower($args[0]) == "ban") {
			$i = $item[0];
			if (isset($item[1])) {
				$i = $i . "#" . $item[1];
			}
			if (in_array($i, $this->items)) {
				$p->sendMessage("§bVật Phẩm Bạn Vừa Ban Đã Có Trong Danh Sách Cấm");
			} else {
				array_push($this->items, $i);
				$this->saveItems();
				$p->sendMessage("§bVật Phẩm Mang ID:§c " . str_replace("#", ":", $i) . " §bĐã Bị Ban");
			}
		} else if (strtolower($args[0]) == "unban") {
			$i = $item[0];
			if (isset($item[1])) {
				$i = $i . "#" . $item[1];
			}
			if (!in_array($i, $this->items)) {
				$p->sendMessage("§bVật Phẩm Bạn Vừa Un Ban Không Có Trong Danh Sách Cấm");
			} else {
				array_splice($this->items, array_search($i, $this->items), 1);
				$this->saveItems();
				$p->sendMessage("§bVật Phẩm Mang ID:§c " . str_replace("#", ":", $i) . " §bĐã Được Un Ban");
			}
		} else if (strtolower($args[0]) == "list") {
			$p->sendMessage("§bDanh Sách Vật Phẩm Bị Ban" . (count($this->items) == 1 ? "" : "") . ": §c" . str_replace("#", ":", implode(", ", $this->items)) . (count($this->items) > 0 ? "." : "§cTrống"));
		} else {
			return false;
		}
		return true;
	}

	public function isBanned($i) {
		if (in_array(strval($i->getID()), $this->items, true) || in_array(($i->getID() . "#" . $i->getDamage()), $this->items, true)) {
			return true;
		}
		return false;
	}

	public function saveItems() {
		if (!isset($this->items)) {
			if (!file_exists($this->getDataFolder() . "items.bin")) {
				@mkdir($this->getDataFolder());
				file_put_contents($this->getDataFolder() . "items.bin", json_encode(array()));
			}
			$this->items = json_decode(file_get_contents($this->getDataFolder() . "items.bin"), true);
		}
		@mkdir($this->getDataFolder());
		file_put_contents($this->getDataFolder() . "items.bin", json_encode($this->items));
	}
}
