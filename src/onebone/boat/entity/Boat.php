<?php

namespace onebone\boat\entity;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\item\Item;

class Boat extends Entity{
  const NETWORK_ID = 90;

  public function __construct(Level $level, CompoundTag $nbt){
  	$this->width = 3;
  	$this->height = 2;
  	parent::__construct($level, $nbt);
  }

  public function spawnTo(Player $player){
    $pk = new AddEntityPacket();
    $pk->entityRuntimeId = $this->getId();
    $pk->type = self::NETWORK_ID;
    $pk->position = $this;
    $pk->motion = null;
    $pk->yaw = 0;
    $pk->pitch = 0;
    $pk->metadata = $this->getDataPropertyManager()->getAll();
    $player->dataPacket($pk);

    parent::spawnTo($player);
  }

  public function attack(EntityDamageEvent $source){
    parent::attack($source);

    if(!$source->isCancelled()){
      $pk = new EntityEventPacket();
  		$pk->eid = $this->id;
  		$pk->event = EntityEventPacket::HURT_ANIMATION;
      foreach($this->getLevel()->getPlayers() as $player){
        $player->dataPacket($pk);
      }
    }
  }

  public function kill(){
    parent::kill();

		foreach($this->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
  }

  public function getDrops(){
    return [
      Item::get(333, 0, 1)
    ];
  }

  public function getSaveId(){
    $class = new \ReflectionClass(static::class);
    return $class->getShortName();
  }
}