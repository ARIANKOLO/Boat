<?php

namespace onebone\boat\item;

use \pocketmine\item\Boat as BoatPM;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use onebone\boat\entity\Boat as BoatEntity;

class Boat extends BoatPM{
  public function __construct($meta = 0, $count = 1){
		parent::__construct($meta);
		$this->setCount($count);
	}

  public function canBeActivated(){
    return true;
  }

  public function onActivate(Player $player, Block $block, Block $target, int $face, Vector3 $clickVector): bool{
    $realPos = $block->getSide($face);

    $boat = new BoatEntity($player->getLevel(), new CompoundTag("", [
  			"Pos" => new ListTag("Pos", [
  				new DoubleTag("", $realPos->getX()),
  				new DoubleTag("", $realPos->getY()),
  				new DoubleTag("", $realPos->getZ())
  			]),
  			"Motion" => new ListTag("Motion", [
  				new DoubleTag("", 0),
  				new DoubleTag("", 0),
  				new DoubleTag("", 0)
  			]),
  			"Rotation" => new ListTag("Rotation", [
  				new FloatTag("", 0),
  				new FloatTag("", 0)
  			]),
  	]));
    $boat->spawnToAll();

    $item = $player->getInventory()->getItemInHand();
    $count = $item->getCount();
    if(--$count <= 0){
      $player->getInventory()->setItemInHand(Item::get(Item::AIR));
      return false;
    }

    $item->setCount($count);
    $player->getInventory()->setItemInHand($item);
    return true;
  }
}
