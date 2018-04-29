<?php

namespace onebone\boat;

use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemFactory;

use onebone\boat\item\Boat as BoatItem;
#use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use onebone\boat\entity\Boat;

class Main extends PluginBase implements Listener{
  private $riding = [];

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    ItemFactory::registerItem(new BoatItem(), true);
    //Item::addCreativeItem(new Item(Item::BOAT));
    $this->getServer()->getCraftingManager()->registerRecipe(new ShapelessRecipe(
      [Item::get(Item::WOODEN_PLANKS, 0, 5), Item::get(Item::WOODEN_SHOVEL, 0, 1)],
      [Item::get(333, 0, 1)]
    ));

    Entity::registerEntity("\\onebone\\boat\\entity\\Boat", true, ["Boat", "minecraft:boat"]);
  }

  public function onQuit(PlayerQuitEvent $event){
    if(isset($this->riding[$event->getPlayer()->getId()])){
      unset($this->riding[$event->getPlayer()->getId()]);
    }
  }

  public function onPacketReceived(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if(!$packet instanceof BatchPacket && !$packet instanceof AnimatePacket && !$packet instanceof MovePlayerPacket && !$packet instanceof PlayerInputPacket){
      var_dump($packet);
    }
    if($packet instanceof InteractPacket){
      echo("Interact");
      $boat = $player->getLevel()->getEntity($packet->target);
      if($boat instanceof Boat){
        echo("Interact on boat");
        var_dump($packet->action);
        if($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE){
          $pk = new SetEntityLinkPacket();
	        $pk->link = new EntityLink($boat->getId(), $player->getId(), EntityLink::TYPE_REMOVE);

          $this->getServer()->broadcastPacket($player->getLevel()->getPlayers(), $pk);
          /*$pk = new SetEntityLinkPacket();
          $pk->link = new EntityLink($boat->getId(), 0, EntityLink::TYPE_REMOVE);
          $player->dataPacket($pk);*/

          if(isset($this->riding[$event->getPlayer()->getId()])){
            unset($this->riding[$event->getPlayer()->getId()]);
          }
        }
      }
    }elseif($packet instanceof InventoryTransactionPacket){
      if($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
        $boat = $player->getLevel()->getEntity($packet->trData->entityRuntimeId);
        if($boat instanceof Boat){
          echo("Transaction on boat\n");
          $pk = new SetEntityLinkPacket();
          $pk->link = new EntityLink($boat->getId(), $player->getId(), EntityLink::TYPE_PASSENGER);

          $this->getServer()->broadcastPacket($player->getLevel()->getPlayers(), $pk);
          /*$pk = new SetEntityLinkPacket();
          $pk->link = new EntityLink($boat->getId(), 0, EntityLink::TYPE_PASSENGER);
          $player->dataPacket($pk);*/

          $this->riding[$player->getId()] = $boat->getId();
        }
      }
    }elseif($packet instanceof MovePlayerPacket){
      if(isset($this->riding[$player->getId()])){
        $boat = $player->getLevel()->getEntity($this->riding[$player->getId()]);
        if($boat instanceof Boat){
          $boat->x = $packet->position->x;
          $boat->y = $packet->position->y;
          $boat->z = $packet->position->z;
        }
      }
    }
  }
}